<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CompanyController extends Controller
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
            $query = Company::query()->select('companies.*');

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
                ->rawColumns(['actions'])
                ->make(true);
        }

        return view('admin.company.index');
    }

    /**
     * Store new company (AJAX).
     */
    public function store(Request $request)
    {
        $rules = [
            'name'  => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'status'=> 'nullable|in:0,1',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        $data = $validator->validated();
        // If your DB doesn't include created_by column for companies, ignore this line or add migration
        if (schema_has_column('companies', 'created_by')) {
            $data['created_by'] = Auth::id();
        }

        $company = Company::create($data);

        if ($request->ajax()) {
            return response()->json(['status' => 'success', 'message' => 'Company created.', 'company' => $company]);
        }

        return redirect()->route('companies.index')->with('success','Company created.');
    }

    /**
     * Return a company's data for show (AJAX).
     */
    public function show(Company $company)
    {
        // If AJAX, return JSON; otherwise blade view (not used here)
        if (request()->ajax()) {
            $company->created_at_formatted = \Carbon\Carbon::parse($company->created_at)->format('Y F j, h:i A');
            return response()->json(['status' => 'success', 'company' => $company]);
        }

        return view('admin.company.show', compact('company'));
    }

    /**
     * Return data for edit modal (AJAX).
     */
    public function edit(Company $company)
    {
        if (request()->ajax()) {
            return response()->json(['status' => 'success', 'company' => $company]);
        }

        return view('admin.company.edit', compact('company'));
    }

    /**
     * Update the company (AJAX).
     */
    public function update(Request $request, Company $company)
    {
        $rules = [
            'name'  => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'status'=> 'nullable|in:0,1',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        $data = $validator->validated();
        if (schema_has_column('companies', 'updated_by')) {
            $data['updated_by'] = Auth::id();
        }

        $company->update($data);

        if ($request->ajax()) {
            return response()->json(['status' => 'success', 'message' => 'Company updated.', 'company' => $company]);
        }

        return redirect()->route('companies.index')->with('success','Company updated.');
    }

    /**
     * Soft delete the company (AJAX).
     */
    public function destroy(Company $company)
    {
        // set deleted_by if column exists
        if (schema_has_column('companies', 'deleted_by')) {
            $company->deleted_by = Auth::id();
            $company->save();
        }

        $company->delete();

        if (request()->ajax()) {
            return response()->json(['status' => 'success', 'message' => 'Company deleted.']);
        }

        return redirect()->route('companies.index')->with('success','Company deleted.');
    }
}

/**
 * Helper function to check column existence. If you prefer, remove this and ensure columns exist.
 */
if (!function_exists('schema_has_column')) {
    function schema_has_column($table, $column)
    {
        try {
            return \Illuminate\Support\Facades\Schema::hasColumn($table, $column);
        } catch (\Throwable $e) {
            return false;
        }
    }
}
