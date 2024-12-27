<?php

namespace App\Http\Controllers;

use App\Models\InvoiceAttachments;
use App\Models\invoices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class InvoicesArchiveController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $invos = invoices::onlyTrashed()->get();
        return view('invoices.invoices_archive', compact('invos'));
    }

   
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $invo = invoices::onlyTrashed()->where('id', $request->invoice_id)->restore();
        session()->flash('restore_invoice');
        return redirect('/invoices');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $invo = invoices::onlyTrashed()->where('id', $request->invoice_id)->first();
        $invo->forceDelete();
        Storage::disk('public_uploads')->deleteDirectory($invo->invoice_number);
        session()->flash('delete_invoice');
        return redirect('/archive');
    }

    public function print_invoice_archive($id)
    {
        $invo = invoices::withTrashed()->where('id', $id)->first();
        return view('invoices.print_invoice', compact('invo'));
    }
}
