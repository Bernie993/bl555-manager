<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Tạm thời return empty view
        return view('audit-logs.index', [
            'auditLogs' => collect([]),
            'users' => collect([]),
            'modelTypes' => []
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show($auditLog)
    {
        // Tạm thời return empty view
        return view('audit-logs.show', [
            'auditLog' => (object)['id' => $auditLog]
        ]);
    }

    /**
     * Export audit logs to CSV.
     */
    public function export(Request $request)
    {
        return response()->json(['message' => 'Export functionality coming soon']);
    }

    /**
     * Get audit logs for a specific model.
     */
    public function getModelAuditLogs(Request $request)
    {
        return response()->json(['data' => []]);
    }
}
