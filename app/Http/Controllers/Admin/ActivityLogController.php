<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with('user');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('entity_type')) {
            $query->where('entity_type', 'like', '%' . $request->entity_type . '%');
        }

        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->date_to . ' 23:59:59');
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('old_values', 'like', '%' . $search . '%')
                  ->orWhere('new_values', 'like', '%' . $search . '%');
            });
        }

        $logs = $query->orderBy('created_at', 'desc')
            ->paginate(50)
            ->appends($request->query());

        $users = User::orderBy('display_name')->get();

        $filters = $request->only(['user_id', 'action', 'entity_type', 'date_from', 'date_to', 'search']);

        return view('admin.activity-log', compact('logs', 'users', 'filters'));
    }
}
