<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SalleAdminController extends Controller
{
    public function show(Request $request)
    {
        $staff = $request->user()->staff;
        abort_unless($staff, 403, 'Reserve au personnel rattache a une salle.');

        return response()->json($staff->salle);
    }

    public function update(Request $request)
    {
        abort_unless($request->user()->hasAnyRole(['gerant', 'super_admin']), 403);
        $staff = $request->user()->staff;
        abort_unless($staff, 403);

        $donnees = $request->validate([
            'nom_salle' => ['sometimes', 'string', 'max:150'],
            'adresse' => ['sometimes', 'string', 'max:255'],
            'ville' => ['sometimes', 'string', 'max:100'],
            'telephone_contact' => ['nullable', 'string', 'max:20'],
        ]);

        $staff->salle->update($donnees);

        return response()->json($staff->salle->fresh());
    }
}