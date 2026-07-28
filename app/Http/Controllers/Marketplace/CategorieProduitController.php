<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Models\CategorieProduit;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategorieProduitController extends Controller
{
    public function index()
    {
        return response()->json(CategorieProduit::orderBy('nom')->get());
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->hasRole('super_admin'), 403);

        $donnees = $request->validate(['nom' => ['required', 'string', 'max:100']]);
        $donnees['slug'] = Str::slug($donnees['nom']);

        $categorie = CategorieProduit::create($donnees);

        return response()->json($categorie, 201);
    }

    public function destroy(Request $request, CategorieProduit $categorie)
    {
        abort_unless($request->user()->hasRole('super_admin'), 403);
        $categorie->delete();

        return response()->json(null, 204);
    }
}
