<?php

namespace App\Http\Controllers;

use App\Models\Salle;
use Illuminate\Http\Request;

class SalleController extends Controller
{
    public function index(Request $request)
    {
        $salles = Salle::query()
            ->when($request->string('ville')->toString(), fn ($q, $ville) => $q->where('ville', 'like', "%{$ville}%"))
            ->when($request->string('recherche')->toString(), fn ($q, $recherche) => $q->where('nom_salle', 'like', "%{$recherche}%"))
            ->with(['formulesAbonnement' => fn ($q) => $q->where('actif', true)])
            ->orderBy('nom_salle')
            ->paginate($request->integer('par_page', 20));

        return response()->json($salles);
    }

    public function show(Salle $salle)
    {
        return response()->json(
            $salle->load(['formulesAbonnement' => fn ($q) => $q->where('actif', true)])
        );
    }
}
