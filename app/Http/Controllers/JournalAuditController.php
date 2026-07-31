<?php

namespace App\Http\Controllers;

use App\Models\JournalAudit;
use Illuminate\Http\Request;

class JournalAuditController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->hasAnyRole(['gerant', 'super_admin']), 403);
        $staff = $request->user()->staff;

        $entrees = JournalAudit::query()
            ->with(['staff.utilisateur', 'salle'])
            ->when(! $request->user()->hasRole('super_admin'), fn ($q) => $q->where('id_salle', $staff->id_salle))
            ->orderByDesc('date_action')
            ->paginate($request->integer('par_page', 20));

        return response()->json($entrees);
    }
}