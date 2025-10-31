<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AuditLog;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with('user')->orderBy('created_at','desc');

        if ($request->filled('table')) {
            $query->where('table_name', $request->table);
        }
        if ($request->filled('record')) {
            $query->where('record_id', $request->record);
        }

        $logs = $query->paginate(30);

        return view('audit_logs.index', compact('logs'));
    }
}
