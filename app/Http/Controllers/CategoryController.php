<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class CategoryController extends Controller
{
    // Show all categories
    public function index(Request $request)
    {

        if ($request->ajax()) {
            $query = Category::select('*');

            return DataTables::eloquent($query)
                ->addIndexColumn() // <-- This generates the auto ID column
                ->addColumn('created_at', function ($row) {
                    return Carbon::parse($row->created_at)->format('Y F j, h:i A');
                })
                ->addColumn('actions', function ($row) {
                    $showBtn = '<button class="btn btn-sm btn-soft-primary btn-show" data-id="'.$row->id.'"><i class="fa-solid fa-eye"></i></button>';
                    $editBtn = '<button class="btn btn-sm btn-soft-warning btn-edit" data-id="'.$row->id.'"><i class="fa-solid fa-edit"></i></button>';
                    $delBtn  = '<button class="btn btn-sm btn-soft-danger btn-delete" data-id="'.$row->id.'"><i class="fa-solid fa-trash"></i></button>';
                    return '<div class="d-flex justify-content-center gap-2">'.$showBtn.$editBtn.$delBtn.'</div>';
                })
                ->rawColumns(['actions','created_at'])
                ->make(true);
        }

        // For initial page load, we need companies list for dropdown
        $category = Category::orderBy('name')->get(['id','name']);
        return view('admin.category.index', compact('category'));

    }


    // Handle create form submission
    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|unique:categories,name',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['status'=>'error','errors'=>$validator->errors()], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        $data = $validator->validated();
        $category = Category::create($data);

        if ($request->ajax()) {
            return response()->json(['status'=>'success','message'=>'Category created.','category'=>$category]);
        }

        return redirect()->route('categories.index')->with('success','Category created.');
    }


    /**
     * Show (AJAX) or blade view.
     */
    public function show(Category $category)
    {
        if (request()->ajax()) {
            
            return response()->json(['status'=>'success','category'=>$category]);
        }
        return view('admin.category.show', compact('category'));
    }

    /**
     * Edit (AJAX) — returns data for modal.
     */
    public function edit(Category $category)
    {
        if (request()->ajax()) {
            return response()->json(['status'=>'success','category'=>$category]);
        }
        return view('admin.category.edit', compact('category'));
    }

    /**
     * Update category (AJAX).
     */
    public function update(Request $request, Category $category)
    {
        $rules = [
            'name' => 'required|unique:categories,name,' . $category->id,
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['status'=>'error','errors'=>$validator->errors()], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        $data = $validator->validated();
        $category->update($data);

        if ($request->ajax()) {
            return response()->json(['status'=>'success','message'=>'Category updated.','category'=>$category]);
        }

        return redirect()->route('categories.index')->with('success','Category updated.');
    }

    /**
     * Soft delete category (AJAX).
     */
    public function destroy(Category $category)
    {
        // optionally set deleted_by if you have column
        if (\Schema::hasColumn('categories', 'deleted_by')) {
            $category->deleted_by = Auth::id();
            $category->save();
        }
        $category->delete();

        if (request()->ajax()) {
            return response()->json(['status'=>'success','message'=>'Category deleted.']);
        }

        return redirect()->route('categories.index')->with('success','Category deleted.');
    }

}
