<?php

namespace App\Http\Controllers;

use App\Models\ProductPrice;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ProductPriceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Index - DataTables server-side
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = ProductPrice::with('product')->select('product_prices.*');

            return DataTables::eloquent($query)
                ->addIndexColumn() // <-- This generates the auto ID column
                ->addColumn('product_name', function ($row) {
                    return $row->product ? $row->product->name : '';
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

        $products = Product::orderBy('name')->get(['id','name']);
        return view('admin.product_price.index', compact('products'));
    }

    /**
     * Store new price (AJAX). Auto-expire previous active price.
     */
    public function store(Request $request)
    {
        $rules = [
            'product_id' => 'required|exists:products,id',
            'price'      => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
        ];

        $v = Validator::make($request->all(), $rules);
        if ($v->fails()) {
            return response()->json(['status'=>'error','errors'=>$v->errors()], 422);
        }

        $productId = $request->product_id;
        $start = Carbon::parse($request->start_date)->startOfDay();
        $end = $request->filled('end_date') ? Carbon::parse($request->end_date)->endOfDay() : null;

        // Overlap detection:
        $overlap = ProductPrice::where('product_id', $productId)
            ->where(function($q) use ($start, $end) {
                if ($end) {
                    // any existing record whose date range intersects [start,end]
                    $q->where(function($qq) use ($start, $end) {
                        $qq->whereBetween('start_date', [$start->toDateString(), $end->toDateString()])
                           ->orWhere(function($qr) use ($start, $end) {
                               $qr->whereNotNull('end_date')
                                  ->whereBetween('end_date', [$start->toDateString(), $end->toDateString()]);
                           })
                           ->orWhere(function($qs) use ($start, $end) {
                               // existing contains new
                               $qs->where('start_date', '<=', $start->toDateString())
                                  ->where(function($q2) use ($end) {
                                      $q2->whereNull('end_date')->orWhere('end_date', '>=', $end->toDateString());
                                  });
                           });
                    });
                } else {
                    // new is open-ended (active). Overlap if any existing that is open-ended or ends after/at start OR starts on/after start
                    $q->where(function($qq) use ($start) {
                        $qq->whereNull('end_date')
                           ->orWhere('end_date', '>=', $start->toDateString())
                           ->orWhere('start_date', '>=', $start->toDateString());
                    });
                }
            })->exists();

        if ($overlap) {
            return response()->json(['status'=>'error','errors'=>['overlap' => ['Price range conflicts with existing price records for this product.']]], 422);
        }

        DB::beginTransaction();
        try {
            // Find previous active price (start < newStart) that is open-ended or ends on/after new start
            $prev = ProductPrice::where('product_id', $productId)
                ->where('start_date', '<', $start->toDateString())
                ->where(function($q){
                    $q->whereNull('end_date')->orWhere('end_date', '>=', DB::raw('CURRENT_DATE')); // we'll check >= newStart below
                })
                ->orderByDesc('start_date')
                ->first();

            // Adjust previous if it overlaps/newStart <= prev.end (or prev has null end)
            if ($prev) {
                // If prev.end_date is null or >= new start, set prev.end_date = newStart -1 day
                if (is_null($prev->end_date) || Carbon::parse($prev->end_date)->gte($start->toDateString())) {
                    $prev->end_date = $start->copy()->subDay()->toDateString();
                    $prev->save();
                }
            }

            $price = ProductPrice::create([
                'product_id' => $productId,
                'price' => $request->price,
                'start_date' => $start->toDateString(),
                'end_date' => $end ? $end->toDateString() : null,
            ]);

            DB::commit();
            return response()->json(['status'=>'success','message'=>'Product price created.','price'=>$price]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['status'=>'error','errors'=>['server'=>[$e->getMessage()]]], 500);
        }
    }

    /**
     * Show (AJAX)
     */
    public function show(ProductPrice $productPrice)
    {
        if (request()->ajax()) {
            $productPrice->load('product');
            $productPrice->created_at_formatted = \Carbon\Carbon::parse($productPrice->created_at)->format('Y F j, h:i A');
            return response()->json(['status'=>'success','price'=>$productPrice]);
        }
        return view('admin.product_price.show', compact('productPrice'));
    }

    /**
     * Edit (AJAX)
     */
    public function edit(ProductPrice $productPrice)
    {
        if (request()->ajax()) {
            return response()->json(['status'=>'success','price'=>$productPrice]);
        }
        return view('admin.product_price.edit', compact('productPrice'));
    }

    /**
     * Update (AJAX) - similar overlap checks and adjust neighbors.
     */
    public function update(Request $request, ProductPrice $productPrice)
    {
        $rules = [
            'product_id' => 'required|exists:products,id',
            'price'      => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
        ];

        $v = Validator::make($request->all(), $rules);
        if ($v->fails()) {
            return response()->json(['status'=>'error','errors'=>$v->errors()], 422);
        }

        $productId = $request->product_id;
        $start = Carbon::parse($request->start_date)->startOfDay();
        $end = $request->filled('end_date') ? Carbon::parse($request->end_date)->endOfDay() : null;

        // Overlap detection excluding current record
        $overlap = ProductPrice::where('product_id', $productId)
            ->where('id', '!=', $productPrice->id)
            ->where(function($q) use ($start, $end) {
                if ($end) {
                    $q->where(function($qq) use ($start, $end) {
                        $qq->whereBetween('start_date', [$start->toDateString(), $end->toDateString()])
                           ->orWhere(function($qr) use ($start, $end) {
                               $qr->whereNotNull('end_date')
                                  ->whereBetween('end_date', [$start->toDateString(), $end->toDateString()]);
                           })
                           ->orWhere(function($qs) use ($start, $end) {
                               $qs->where('start_date', '<=', $start->toDateString())
                                  ->where(function($q2) use ($end) {
                                      $q2->whereNull('end_date')->orWhere('end_date', '>=', $end->toDateString());
                                  });
                           });
                    });
                } else {
                    $q->where(function($qq) use ($start) {
                        $qq->whereNull('end_date')
                           ->orWhere('end_date', '>=', $start->toDateString())
                           ->orWhere('start_date', '>=', $start->toDateString());
                    });
                }
            })->exists();

        if ($overlap) {
            return response()->json(['status'=>'error','errors'=>['overlap' => ['Price range conflicts with existing price records for this product.']]], 422);
        }

        DB::beginTransaction();
        try {
            // If product changed or start changed, we may need to adjust the previous price
            // Find previous price (different from this one) that starts before new start and ends null or >= newStart
            $prev = ProductPrice::where('product_id', $productId)
                ->where('id', '!=', $productPrice->id)
                ->where('start_date', '<', $start->toDateString())
                ->orderByDesc('start_date')
                ->first();

            if ($prev) {
                if (is_null($prev->end_date) || Carbon::parse($prev->end_date)->gte($start)) {
                    $prev->end_date = $start->copy()->subDay()->toDateString();
                    $prev->save();
                }
            }

            // Update current record
            $productPrice->update([
                'product_id' => $productId,
                'price' => $request->price,
                'start_date' => $start->toDateString(),
                'end_date' => $end ? $end->toDateString() : null,
            ]);

            DB::commit();
            return response()->json(['status'=>'success','message'=>'Product price updated.','price'=>$productPrice]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['status'=>'error','errors'=>['server'=>[$e->getMessage()]]], 500);
        }
    }

    /**
     * Destroy (soft delete)
     */
    public function destroy(ProductPrice $productPrice)
    {
        if (\Schema::hasColumn('product_prices', 'deleted_by')) {
            $productPrice->deleted_by = Auth::id();
            $productPrice->save();
        }
        $productPrice->delete();

        if (request()->ajax()) {
            return response()->json(['status'=>'success','message'=>'Product price deleted.']);
        }
        return redirect()->route('product-prices.index')->with('success','Product price deleted.');
    }

    public function getByProduct($productId)
    {
        $price = ProductPrice::where('product_id', $productId)
            ->where(function ($q) {
                $q->whereNull('end_date')
                ->orWhere('end_date', '>=', now()->toDateString());
            })
            ->orderBy('start_date', 'desc')
            ->first();

        if (!$price) {
            return response()->json([
                'price' => null,
                'status' => 'no_price',
                'message' => 'No active price found for this product'
            ]);
        }

        return response()->json([
            'price' => $price->price,
            'status' => 'success'
        ]);
    }


}
