<?php

namespace App\Http\Controllers;

use App\Models\InvoiceAttachments;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvoiceAttachmentsController extends Controller
{
   
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [

            'file_name' => 'mimes:pdf,jpeg,png,jpg',

        ], [
            'file_name.mimes' => 'صيغة المرفق يجب ان تكون   pdf, jpeg , png , jpg',
        ]);

        $attachment = $request->file('attach');
        $file_name = $attachment->getClientOriginalName();
        $invoice_number = $request->invoice_number;

        $attachments = new InvoiceAttachments();
        $attachments->file_name = $file_name;
        $attachments->invoice_number = $invoice_number;
        $attachments->created_by = Auth::user()->name;
        $attachments->invoice_id = $request->invoice_id;
        $attachments->save();

        // move attach
        $attachmentName = $request->attach->getClientOriginalName();
        $request->attach->move('Attachments/' . $invoice_number, $attachmentName);


        session()->flash('Add', 'تم اضافة المرفق بنجاح');
        return back();
    }

}
