<?php

namespace App\Http\Controllers;

use App\Models\SupplyRecord;
use App\Models\Company;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class SupplyRecordController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display DataTable or page
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = SupplyRecord::with(['company','location','product','creator'])->select('supply_records.*');

            // Filters
            if ($request->filled('company_id')) {
                $query->where('company_id', $request->company_id);
            }
            if ($request->filled('product_id')) {
                $query->where('product_id', $request->product_id);
            }
            if ($request->filled('month')) {
                $month = (int)$request->month;
                $year = (int)($request->year ?? date('Y'));
                $query->whereMonth('supply_date', $month)->whereYear('supply_date', $year);
            } elseif ($request->filled('year')) {
                $query->whereYear('supply_date', (int)$request->year);
            }

            return DataTables::eloquent($query)
                ->addIndexColumn() // <-- This generates the auto ID column
                ->addColumn('company_name', fn($r) => $r->company->name ?? '')
                ->addColumn('location_name', fn($r) => $r->location->address_line ?? '')
                ->addColumn('product_name', fn($r) => $r->product->name ?? '')
                ->addColumn('total_amount_format', fn($r) => number_format($r->total_amount, 4))
                ->addColumn('added_by', fn($r) => $r->creator->name ?? '')
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

        $companies = Company::orderBy('name')->get(['id','name']);
        $products = Product::orderBy('name')->get(['id','name']);

        return view('admin.supply_record.index', compact('companies','products'));
    }

    /**
     * Store record (AJAX)
     * total_amount ignored – DB auto calculates using storedAs()
     */
    public function store(Request $request)
    {
        $rules = [
            'company_id' => 'required|exists:companies,id',
            'location_id' => 'nullable|exists:locations,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0',
            'unit_price' => 'required|numeric|min:0',
            'supply_date' => 'nullable|date',
        ];

        $v = Validator::make($request->all(), $rules);
        if ($v->fails()) {
            return response()->json(['status'=>'error','errors'=>$v->errors()], 422);
        }

        $data = $v->validated();

        // force remove total_amount if someone tries to send it
        unset($data['total_amount']);

        $data['created_by'] = Auth::id();

        $record = SupplyRecord::create($data);

        return response()->json(['status'=>'success','message'=>'Supply record created.','record'=>$record]);
    }

    /**
     * Show record
     */
    public function show(SupplyRecord $supplyRecord)
    {
        if (request()->ajax()) {
            $supplyRecord->load('company','location','product','creator','updater','deleter');
            return response()->json(['status'=>'success','record'=>$supplyRecord]);
        }

        return view('admin.supply_record.show', compact('supplyRecord'));
    }

    /**
     * Edit record
     */
    public function edit(SupplyRecord $supplyRecord)
    {
        if (request()->ajax()) {
            return response()->json(['status'=>'success','record'=>$supplyRecord]);
        }

        return view('admin.supply_record.edit', compact('supplyRecord'));
    }

    /**
     * Update record
     * total_amount ignored – DB recalculates automatically
     */
    public function update(Request $request, SupplyRecord $supplyRecord)
    {
        $rules = [
            'company_id' => 'required|exists:companies,id',
            'location_id' => 'nullable|exists:locations,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0',
            'unit_price' => 'required|numeric|min:0',
            'supply_date' => 'nullable|date',
        ];

        $v = Validator::make($request->all(), $rules);
        if ($v->fails()) {
            return response()->json(['status'=>'error','errors'=>$v->errors()], 422);
        }

        $data = $v->validated();

        unset($data['total_amount']); // do not allow override

        $data['updated_by'] = Auth::id();

        $supplyRecord->update($data);

        return response()->json(['status'=>'success','message'=>'Supply record updated.','record'=>$supplyRecord]);
    }

    /**
     * Soft Delete
     */
    public function destroy(SupplyRecord $supplyRecord)
    {
        $supplyRecord->deleted_by = Auth::id();
        $supplyRecord->save();
        $supplyRecord->delete();

        return response()->json(['status'=>'success','message'=>'Supply record deleted.']);
    }

    /**
     * Get locations for a company
     */
    public function getLocations(Company $company)
    {
        $locations = $company->locations()->select('id','address_line','city','state','zip_code')->get();
        return response()->json(['status'=>'success','locations'=>$locations]);
    }

    /**
     * Get Product Price on Date
     */
    public function getPrice(Request $request)
    {
        $v = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'date' => 'nullable|date',
        ]);
        if ($v->fails()) {
            return response()->json(['status'=>'error','errors'=>$v->errors()], 422);
        }

        $productId = $request->product_id;
        $date = $request->date ? date('Y-m-d', strtotime($request->date)) : date('Y-m-d');

        $price = \App\Models\ProductPrice::where('product_id', $productId)
            ->where('start_date', '<=', $date)
            ->where(function($q) use ($date) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', $date);
            })
            ->orderByDesc('start_date')
            ->first();

        return response()->json(['status'=>'success','price'=>$price->price ?? null]);
    }
}
