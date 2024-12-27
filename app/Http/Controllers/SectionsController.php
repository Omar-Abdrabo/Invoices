<?php

namespace App\Http\Controllers;

use App\Models\Products;
use App\Models\sections;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SectionsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $sections = sections::all();
        //return view('sections.section',compact('sections'));
        return view('sections.section', ['sections' => $sections]);
        //return view('sections.section')->with('sections',$sections);
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
            'section_name' => 'required|unique:sections|max:255',
            'description' => 'required',
        ], [
            'section_name.required' => 'يجب ادخال اسم القسم',
            'section_name.unique' => 'اسم القسم مسجل مسبقا',
            'description.required' => 'يرجي ادخال البيان',
        ]);

        sections::create([
            'section_name' => $request->section_name,
            'description' => $request->description,
            'created_by' => Auth::user()->name,
        ]);
        session()->flash('Add', 'تم اضافة القسم بنجاح');
        return redirect('/sections');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\sections  $sections
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $id = $request->id;
        $this->validate($request, [
            'section_name' => 'required|max:255|unique:sections,section_name,' . $id,
            'description' => 'required',
        ], [
            'section_name.required' => 'يرجي ادخال اسم القسم',
            'section_name.unique' => 'اسم القسم مسجل مسبقا',
            'description.required' => 'يرجي ادخال البيان',
        ]);

        $section = sections::find($id);
        $section->update([
            'section_name' => $request->section_name,
            'description' => $request->description,
        ]);
        session()->flash('edit', 'تم تعديل القسم بنجاح');

        return redirect('/sections');
        //    $section->section_name =$request->section_name;
        //    $section->description = $request->description;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\sections  $sections
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $id = $request->id;
        sections::find($id)->delete();
        session()->flash('delete', 'تم حذف القسم بنجاح');
        return redirect('/sections');
    }

}
