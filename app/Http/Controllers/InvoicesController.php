<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\invoices;
use App\Models\sections;
use Illuminate\Http\Request;
use App\Exports\InvoicesExport;
use App\Models\InvoicesDetails;
use App\Models\InvoiceAttachments;
use Illuminate\Support\Facades\DB;
use App\Notifications\Invoice_Added;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;

class InvoicesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $allInvoices = invoices::all();
        return view('invoices.invoices', ['allInvoices' => $allInvoices]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $sections = sections::all();
        return view('invoices.add_invoice', compact('sections'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $section_name = sections::where('id', $request->Section)->first()->section_name;
        invoices::create([
            'invoice_number' => $request->invoice_number,
            'invoice_date' => $request->invoice_date,
            'due_date' => $request->due_date,
            'product' => $request->product,
            'section' => $section_name,
            'section_id' => $request->Section,
            'discount' => $request->discount,
            'rate_vat' => $request->rate_vat,
            'amount_collection' => $request->amount_collection,
            'amount_commission' => $request->amount_commission,
            'value_vat' => $request->value_vat,
            'total' => $request->total,
            'status' => 'غير مدفوعه',
            'value_status' => 2,
            'note' => $request->note,
            'user' => Auth::user()->name,
        ]);
        $invoice_id = invoices::latest()->first()->id;
        InvoicesDetails::create([
            'invoice_id' => $invoice_id,
            'invoice_number' => $request->invoice_number,
            'product' => $request->product,
            'section' => $section_name,
            'status' => 'غير مدفوعه',
            'value_status' => 2,
            'note' => $request->note,
            'user' => Auth::user()->name,
        ]);
        if ($request->hasFile('attach')) {
            $invoice_id = Invoices::latest()->first()->id;
            $attachment = $request->file('attach');
            $file_name = $attachment->getClientOriginalName();
            $invoice_number = $request->invoice_number;
            $attachments = new InvoiceAttachments();
            $attachments->file_name = $file_name;
            $attachments->invoice_number = $invoice_number;
            $attachments->Created_by = Auth::user()->name;
            $attachments->invoice_id = $invoice_id;
            $attachments->save();
            // move attach
            $attachmentName = $request->attach->getClientOriginalName();
            $request->attach->move('Attachments/' . $invoice_number, $attachmentName);
        }

        // E-MAIL
        // $user = User::first();
        // Notification::send($user, new AddInvoice($invoice_id));
        // // $user->notify(new AddInvoice($invoice_id));
        // DATABASE
        $invoices = invoices::latest()->first();
        $user = User::get(); // send notification to every user in data base
        // $user = User::find(Auth::id());//send notification to user who create the invoice 
        Notification::send($user, new Invoice_Added($invoices));
        // $user->notify(new Invoice_Added($invoices));
        session()->flash('Add', 'تم اضافة الفاتورة بنجاح');
        return back();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\invoices  $invoices
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $invoice = invoices::findOrFail($id);
        return view("invoices.status_update", ['invoice' => $invoice]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\invoices  $invoices
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $invoice = invoices::where('id', $id)->first();
        $sections = sections::all();
        return view('invoices.edit_invoice', ['invoice' => $invoice, 'sections' => $sections]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\invoices  $invoices
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $invoice = invoices::findOrFail($request->invoice_id);
        $invoice->update([
            'invoice_number' => $request->invoice_number,
            'invoice_date' => $request->invoice_date,
            'due_date' => $request->due_date,
            'product' => $request->product,
            'section' => sections::where('id', $request->Section)->first()->section_name,
            'section_id' => $request->Section,
            'discount' => $request->discount,
            'rate_vat' => $request->rate_vat,
            'amount_collection' => $request->amount_collection,
            'amount_commission' => $request->amount_commission,
            'value_vat' => $request->value_vat,
            'total' => $request->total,
            // 'status' => $request->status,
            // 'value_status' => $request->value_status,
            'note' => $request->note,
            // 'user' => Auth::user()->name,
        ]);

        InvoicesDetails::create([
            'invoice_id' => $request->invoice_id,
            'invoice_number' => $request->invoice_number,
            'product' => $request->product,
            'section' => sections::where('id', $request->Section)->first()->section_name,
            'status' => $invoice->status,
            'value_status' => $invoice->value_status,
            'note' => $request->note,
            'user' => Auth::user()->name,
        ]);

        session()->flash('edit', 'تم تعديل الفاتورة بنجاح');
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\invoices  $invoices
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $invoice = invoices::where('id', $request->invoice_id)->first();
        $invoice_attachs = InvoiceAttachments::where('invoice_id', $request->invoice_id)->first();
        if (!$request->id_page == 2) {
            if (!empty($invoice_attachs->invoice_number)) {
                Storage::disk('public_uploads')->deleteDirectory($invoice_attachs->invoice_number);
            }
            $invoice->forceDelete();
            session()->flash('delete_invoice');
            return redirect('/invoices');
        } else {
            //soft delete
            $invoice->delete();
            session()->flash('archive_invoice');
            return redirect('/archive');
        }
    }

    public function getProducts($id)
    {
        $states = DB::table('products')->where('section_id', $id)->pluck('product_name', 'id');
        return json_encode($states);
    }

    public function status_update($id, Request $request)
    {
        $invoice = invoices::find($id);
        if ($request->status == 'مدفوعة') {
            $invoice->update([
                'value_status' => 1,
                'status' => $request->status,
            ]);

            InvoicesDetails::create([
                'invoice_id' => $request->invoice_id,
                'invoice_number' => $request->invoice_number,
                'product' => $request->product,
                'section' => sections::where('id', $request->Section)->first()->section_name,
                'status' => ' مدفوعة',
                'value_status' => 1,
                'note' => $request->note,
                'payment_date' => $request->payment_date,
                'user' => Auth::user()->name,
            ]);
        } elseif ($request->status == "مدفوعة جزئيا") {
            $invoice->update([
                'value_status' => 3,
                'status' => $request->status,
            ]);

            InvoicesDetails::create([
                'invoice_id' => $request->invoice_id,
                'invoice_number' => $request->invoice_number,
                'product' => $request->product,
                'section' => sections::where('id', $request->Section)->first()->section_name,
                'status' => "مدفوعة جزئيا",
                'value_status' => 3,
                'note' => $request->note,
                'payment_date' => $request->payment_date,
                'user' => Auth::user()->name,
            ]);
        }
        session()->flash('status_update');
        return redirect('/invoices');
    }

    public function paid_invoices()
    {
        $invos = invoices::where('value_status', 1)->get();
        return view('invoices.paid_invoices', compact('invos'));
    }

    public function unpaid_invoices()
    {
        $invos = invoices::where('value_status', 2)->get();
        return view('invoices.unpaid_invoices', compact('invos'));
    }

    public function partial_invoices()
    {
        $invos = invoices::where('value_status', 3)->get();
        return view('invoices.partial_invoices', compact('invos'));
    }

    public function print_invoice($id)
    {
        $invo = invoices::where('id', $id)->first();
        return view('invoices.print_invoice', compact('invo'));
    }

    public function export()
    {
        return Excel::download(new InvoicesExport, 'invoices.xlsx');
    }

    public function mark_all_as_read(Request $request)
    {
        $userUnreadNotification = auth()->user()->unreadNotifications;
        if ($userUnreadNotification) {
            $userUnreadNotification->markAsRead();
        }
        return back();
    }

    public function unreadNotifications_count()
    {
        return auth()->user()->unreadNotifications->count();
    }

    public function unreadNotifications()
    {
        foreach (auth()->user()->unreadNotifications as $notification) {
            return $notification->data['title'];
        }
    }
}
