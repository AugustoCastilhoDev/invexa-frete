<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class AuditoriaController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::query()->with('causer')->latest();

        if ($logName = $request->input('log_name')) {
            $query->where('log_name', $logName);
        }

        if ($evento = $request->input('event')) {
            $query->where('event', $evento);
        }

        if ($dataInicio = $request->input('data_inicio')) {
            $query->whereDate('created_at', '>=', $dataInicio);
        }

        if ($dataFim = $request->input('data_fim')) {
            $query->whereDate('created_at', '<=', $dataFim);
        }

        $registros = $query->paginate(30)->withQueryString();

        $logNames = ActivityLog::query()->distinct()->orderBy('log_name')->pluck('log_name');

        return view('auditoria.index', compact('registros', 'logNames'));
    }
}
