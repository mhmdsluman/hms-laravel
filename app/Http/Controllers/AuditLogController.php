<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AuditLogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $logs = AuditLog::with('user')
            ->latest()
            ->paginate(20);

        return Inertia::render('Admin/AuditTrail/Index', [
            'logs' => $logs,
        ]);
    }
}
