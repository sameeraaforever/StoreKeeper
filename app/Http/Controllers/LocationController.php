<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\Company;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class LocationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display listing (DataTables server-side).
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Location::with('company')->select('locations.*');

            return DataTables::eloquent($query)
                ->addIndexColumn() // <-- This generates the auto ID column
                ->addColumn('company_name', function ($row) {
                    return $row->company ? $row->company->name : '';
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

        // For initial page load, we need companies list for dropdown
        $companies = Company::orderBy('name')->get(['id','name']);
        return view('admin.location.index', compact('companies'));
    }

    /**
     * Store a new location (AJAX).
     */
    public function store(Request $request)
    {
        $rules = [
            'company_id'   => 'required|exists:companies,id',
            'address_line' => 'nullable|string|max:255',
            'city'         => 'nullable|string|max:100',
            'state'        => 'nullable|string|max:100',
            'zip_code'     => 'nullable|string|max:50',
            'country'      => 'nullable|string|max:100',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['status'=>'error','errors'=>$validator->errors()], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        $data = $validator->validated();
        $location = Location::create($data);

        if ($request->ajax()) {
            return response()->json(['status'=>'success','message'=>'Location created.','location'=>$location]);
        }

        return redirect()->route('locations.index')->with('success','Location created.');
    }

    /**
     * Show (AJAX) or blade view.
     */
    public function show(Location $location)
    {
        if (request()->ajax()) {
            $location->load('company');
            $location->created_at_formatted = \Carbon\Carbon::parse($location->created_at)->format('Y F j, h:i A');
            return response()->json(['status'=>'success','location'=>$location]);
        }
        return view('admin.location.show', compact('location'));
    }

    /**
     * Edit (AJAX) — returns data for modal.
     */
    public function edit(Location $location)
    {
        if (request()->ajax()) {
            return response()->json(['status'=>'success','location'=>$location]);
        }
        return view('admin.location.edit', compact('location'));
    }

    /**
     * Update location (AJAX).
     */
    public function update(Request $request, Location $location)
    {
        $rules = [
            'company_id'   => 'required|exists:companies,id',
            'address_line' => 'nullable|string|max:255',
            'city'         => 'nullable|string|max:100',
            'state'        => 'nullable|string|max:100',
            'zip_code'     => 'nullable|string|max:50',
            'country'      => 'nullable|string|max:100',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['status'=>'error','errors'=>$validator->errors()], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        $data = $validator->validated();
        $location->update($data);

        if ($request->ajax()) {
            return response()->json(['status'=>'success','message'=>'Location updated.','location'=>$location]);
        }

        return redirect()->route('locations.index')->with('success','Location updated.');
    }

    /**
     * Soft delete location (AJAX).
     */
    public function destroy(Location $location)
    {
        // optionally set deleted_by if you have column
        if (\Schema::hasColumn('locations', 'deleted_by')) {
            $location->deleted_by = Auth::id();
            $location->save();
        }
        $location->delete();

        if (request()->ajax()) {
            return response()->json(['status'=>'success','message'=>'Location deleted.']);
        }

        return redirect()->route('locations.index')->with('success','Location deleted.');
    }
}
