<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\ProductPrice;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
                ->addColumn('stock_qty', function ($row) {
                    return number_format($row->stock_qty);
                })
                ->addColumn('actions', function ($row) {
                    $stockBtn = '<button class="btn btn-sm btn-soft-success btn-stock" data-id="'.$row->id.'" data-name="'.$row->name.'" title="Stock In"><i class="fa-solid fa-plus"></i></button>';
                    $stockOutBtn = '<button class="btn btn-sm btn-soft-danger btn-stock-out" data-id="'.$row->id.'" data-name="'.$row->name.'" title="Stock Out"><i class="fa-solid fa-minus"></i></button>';
                    $showBtn = '<button class="btn btn-sm btn-soft-primary btn-show" data-id="'.$row->id.'"><i class="fa-solid fa-eye"  title="View"></i></button>';
                    $editBtn = '<button class="btn btn-sm btn-soft-warning btn-edit" data-id="'.$row->id.'"><i class="fa-solid fa-edit" title="Edit"></i></button>';
                    $delBtn  = '<button class="btn btn-sm btn-soft-danger btn-delete" data-id="'.$row->id.'"><i class="fa-solid fa-trash" title="Delete"></i></button>';
                    return '<div class="d-flex justify-content-center gap-2">'.$stockBtn.$stockOutBtn.$showBtn.$editBtn.$delBtn.'</div>';
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

    public function stockIn(Request $request)
    {
        $rules = [
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|numeric|min:0.001',
            'price'      => 'nullable|numeric|min:0',
        ];

        $v = Validator::make($request->all(), $rules);
        if ($v->fails()) {
            return response()->json(['status'=>'error','errors'=>$v->errors()], 422);
        }

        $product = Product::findOrFail($request->product_id);
        $quantity = (float) $request->quantity;
        $newPrice = $request->filled('price') ? (float) $request->price : null;

        // If product has no active price and no price provided => require it
        $activePrice = ProductPrice::where('product_id', $product->id)
            ->where(function($q){
                $q->whereNull('end_date')
                ->orWhere('end_date', '>=', now()->toDateString());
            })
            ->orderByDesc('start_date')
            ->first();

        if (!$activePrice && is_null($newPrice)) {
            return response()->json(['status'=>'error','message'=>'No active price found for this product. Please enter price.'], 422);
        }

        DB::beginTransaction();
        try {
            // 1) Increase stock
            $product->stock_qty = $product->stock_qty + $quantity;
            $product->save();

            // 2) Price handling (Option B behavior: only create new price record when different)
            // New price start_date is always today (no backdate)
            $today = Carbon::today();
            if (!is_null($newPrice)) {
                $createNewPrice = false;

                if ($activePrice) {
                    // Compare numeric values (taking care of precision)
                    if (bccomp((string)$activePrice->price, (string)$newPrice, 4) !== 0) {
                        $createNewPrice = true;
                    }
                } else {
                    // No active price exists -> must create
                    $createNewPrice = true;
                }

                if ($createNewPrice) {
                    // Close previous active price if exists: set end_date = today - 1 (E1)
                    if ($activePrice) {
                        $activePrice->end_date = $today->copy()->subDay()->toDateString();
                        $activePrice->save();
                    }

                    // Create new ProductPrice with start_date = today, end_date = null
                    ProductPrice::create([
                        'product_id' => $product->id,
                        'price' => $newPrice,
                        'start_date' => $today->toDateString(),
                        'end_date' => null,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Stock added successfully!',
                'new_stock' => $product->stock_qty
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('StockIn error: '.$e->getMessage());
            return response()->json(['status'=>'error','message'=>'Server error: '.$e->getMessage()], 500);
        }
    }


    public function stockOut(Request $request)
    {

        $rules = [
            'stock_out_product_id' => 'required|exists:products,id',
            'stock_out_quantity'   => 'required|numeric|min:0.001'
        ];

        $v = Validator::make($request->all(), $rules);
        if ($v->fails()) {
            return response()->json(['status'=>'error','errors'=>$v->errors()], 422);
        }

        $product = Product::findOrFail($request->stock_out_product_id);
        $quantity = (float) $request->stock_out_quantity;

        try {
            // 1) Decrease stock
            $product->stock_qty = $product->stock_qty - $quantity;
            $product->update(['stock_qty'=>$product->stock_qty]);

            return response()->json([
                'status' => 'success',
                'message' => 'Stock subtracted successfully!',
                'new_stock' => $product->stock_qty
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('StockOut error: '.$e->getMessage());
            return response()->json(['status'=>'error','message'=>'Server error: '.$e->getMessage()], 500);
        }

    }


    public function getStock(Product $product)
    {
        return response()->json([
            'stock_qty' => number_format($product->stock_qty,0)
        ]);
    }

}
