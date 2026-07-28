<?php

namespace App\Http\Controllers;

use App\Models\BannierePublicitaire;
use Illuminate\Http\Request;

class BanniereController extends Controller
{
    public function index()
    {
        return response()->json(BannierePublicitaire::actives()->get());
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->hasRole('super_admin'), 403);

        $donnees = $request->validate([
            'titre' => ['required', 'string', 'max:150'],
            'image' => ['required', 'string', 'max:255'],
            'lien_url' => ['nullable', 'string', 'max:255'],
            'ordre_affichage' => ['nullable', 'integer', 'min:0'],
            'date_debut' => ['nullable', 'date'],
            'date_fin' => ['nullable', 'date', 'after_or_equal:date_debut'],
        ]);

        $banniere = BannierePublicitaire::create($donnees);

        return response()->json($banniere, 201);
    }

    public function update(Request $request, BannierePublicitaire $banniere)
    {
        abort_unless($request->user()->hasRole('super_admin'), 403);

        $donnees = $request->validate([
            'titre' => ['sometimes', 'string', 'max:150'],
            'image' => ['sometimes', 'string', 'max:255'],
            'lien_url' => ['nullable', 'string', 'max:255'],
            'ordre_affichage' => ['sometimes', 'integer', 'min:0'],
            'actif' => ['sometimes', 'boolean'],
            'date_debut' => ['nullable', 'date'],
            'date_fin' => ['nullable', 'date', 'after_or_equal:date_debut'],
        ]);

        $banniere->update($donnees);

        return response()->json($banniere);
    }

    public function destroy(Request $request, BannierePublicitaire $banniere)
    {
        abort_unless($request->user()->hasRole('super_admin'), 403);
        $banniere->delete();

        return response()->json(null, 204);
    }
}
