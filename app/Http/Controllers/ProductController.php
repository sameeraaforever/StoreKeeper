<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    // fixed unit list (Cleaning Industry Standard Units)
    protected $units = [
        'Bottle','Litre','Gallon','Millilitre','Pack','Box','Kg','Gram','Pieces'
    ];

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Index - DataTables server side
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Product::with('category')->select('products.*');

            return DataTables::eloquent($query)
                ->addIndexColumn() // <-- This generates the auto ID column
                ->addColumn('category_name', function ($row) {
                    return $row->category ? $row->category->name : '';
                })
                ->addColumn('created_at', function ($row) {
                    return Carbon::parse($row->created_at)->format('Y F j, h:i A');
                })
                ->addColumn('actions', function ($row) {
                    $showBtn = '<button class="btn btn-sm btn-soft-primary btn-show" data-id="'.$row->id.'"><i class="fa-solid fa-eye"></i></button>';
                    $editBtn = '<button class="btn btn-sm btn-soft-warning btn-edit" data-id="'.$row->id.'"><i class="fa-solid fa-edit"></i></button>';
                    $delBtn  = '<button class="btn btn-sm btn-soft-danger btn-delete" data-id="'.$row->id.'"><i class="fa-solid fa-trash"></i></button>';
                    return '<div class="d-flex justify-content-center gap-2">'.$showBtn.$editBtn.$delBtn.'</div>';
                })
                ->rawColumns(['actions'])
                ->make(true);
        }

        $categories = Category::orderBy('name')->get(['id','name']);
        $units = $this->units;

        return view('admin.product.index', compact('categories','units'));
    }

    /**
     * Store new product (AJAX)
     */
    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'unit' => 'nullable|string|in:'.implode(',', $this->units),
            'description' => 'nullable|string',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['status'=>'error','errors'=>$validator->errors()], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        $data = $validator->validated();
        $product = Product::create($data);

        if ($request->ajax()) {
            return response()->json(['status'=>'success','message'=>'Product created.','product'=>$product]);
        }

        return redirect()->route('products.index')->with('success','Product created.');
    }

    /**
     * Show product (AJAX)
     */
    public function show(Product $product)
    {
        if (request()->ajax()) {
            $product->load('category');
            $product->created_at_formatted = \Carbon\Carbon::parse($product->created_at)->format('Y F j, h:i A');
            return response()->json(['status'=>'success','product'=>$product]);
        }
        return view('admin.product.show', compact('product'));
    }

    /**
     * Edit (AJAX)
     */
    public function edit(Product $product)
    {
        if (request()->ajax()) {
            return response()->json(['status'=>'success','product'=>$product]);
        }
        return view('admin.product.edit', compact('product'));
    }

    /**
     * Update product (AJAX)
     */
    public function update(Request $request, Product $product)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'unit' => 'nullable|string|in:'.implode(',', $this->units),
            'description' => 'nullable|string',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['status'=>'error','errors'=>$validator->errors()], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        $product->update($validator->validated());

        if ($request->ajax()) {
            return response()->json(['status'=>'success','message'=>'Product updated.','product'=>$product]);
        }

        return redirect()->route('products.index')->with('success','Product updated.');
    }

    /**
     * Soft delete (AJAX)
     */
    public function destroy(Product $product)
    {
        if (\Schema::hasColumn('products','deleted_by')) {
            $product->deleted_by = Auth::id();
            $product->save();
        }
        $product->delete();

        if (request()->ajax()) {
            return response()->json(['status'=>'success','message'=>'Product deleted.']);
        }

        return redirect()->route('products.index')->with('success','Product deleted.');
    }
}
