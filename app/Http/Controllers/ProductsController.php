<?php

namespace App\Http\Controllers;

use App\Models\Products;
use App\Models\sections;
use Illuminate\Http\Request;

class ProductsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $sections = sections::all();
        $products = Products::all();
        return view('products.products', ['products' => $products, 'sections' => $sections]);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_name' => 'required|max:255',
            'section_id' => 'required',
            'description' => 'required',
        ], [
            'product_name.required' => 'يجب ادخال اسم المنتج',
            'description.required' => 'يرجي ادخال البيان',
            'section_id.required' => 'يجب اختيار اسم القسم'
        ]);

        Products::create([
            'product_name' => $request->product_name,
            'section_id' => $request->section_id,
            'description' => $request->description
        ]);
        session()->flash('Add', 'تم اضافة المنتج بنجاح');
        return redirect('/products');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Products  $products
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $section_id = sections::where('section_name', $request->section_name)->first()->id;
        $product = Products::findOrFail($request->product_id);
        $product->update([
            'product_name' => $request->product_name,
            'section_id' => $section_id,
            'description' => $request->description
        ]);
        session()->flash('Edit', 'تم تعديل المنتج بنجاح');
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Products  $products
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $product = Products::findOrFail($request->product_id);
        $product->delete();
        session()->flash('delete', 'تم حذف المنتج بنجاح');
        return back();
    }
}
