<?php

namespace App\Http\Controllers;

use App\Models\InvoiceAttachments;
use App\Models\invoices;
use App\Models\InvoicesDetails;
use GuzzleHttp\Psr7\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Notification;

class InvoicesDetailsController extends Controller
{
   

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\InvoicesDetails  $invoicesDetails
     * @return \Illuminate\Http\Response
     */
    public function edit($id, $notification_id = null)
    {

        if ($notification_id) {
            $notification = auth()->user()->notifications()->where('id', $notification_id)->first();
            $notification->markAsRead();
        }

        $invoices = invoices::where('id', $id)->first();
        $invoice_details = InvoicesDetails::where('invoice_id', $id)->get();
        $invoice_attachs = InvoiceAttachments::where('invoice_id', $id)->get();

        return view('invoices.invoice_details', compact('invoice_details', 'invoices', 'invoice_attachs'));
    }
    
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\InvoicesDetails  $invoicesDetails
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $invoices = InvoiceAttachments::findOrFail($request->file_id);
        $invoices->delete();

        $st = "Attachments";
        $file_path = public_path($st . '/' . $request->invoice_number . '/' . $request->file_name);
        File::delete($file_path);
        // $result = Storage::disk('public_uploads')->delete($file_path);
        // dd([$result, $file_path]);

        session()->flash('delete', 'تم حذف المرفق بنجاح');
        return back();
    }

    public function open_file($invoice_number, $file_name)
    {
        $st = "Attachments";
        $file_path = public_path($st . '/' . $invoice_number . '/' . $file_name);
        return response()->file($file_path);
        // $files = Storage::disk('public_uploads')->getDriver()->getAdapter()->applyPathPrefix($invoice_number . '/' . $file_name);
        // return response()->file($files);
    }

    public function get_file($invoice_number, $file_name)
    {
        $st = "Attachments";
        $file_path = public_path($st . '/' . $invoice_number . '/' . $file_name);
        return response()->download($file_path);

        // return response()->download(storage_path('Attachments' . $invoice_number . '/' . $file_name));
        // $contents = Storage::disk('public_uploads')->getDriver()->getAdapter()->applyPathPrefix($invoice_number . '/' . $file_name);
        // return response()->download($contents);
    }
}
