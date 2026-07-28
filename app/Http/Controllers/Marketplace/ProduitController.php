<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Models\Produit;
use Illuminate\Http\Request;

class ProduitController extends Controller
{
    public function index(Request $request)
    {
        $produits = Produit::query()
            ->where('actif', true)
            ->whereHas('boutique', fn ($q) => $q->where('actif', true))
            ->with(['boutique:id_boutique,nom,logo,ville', 'categorie:id_categorie,nom,slug'])
            ->when($request->integer('id_categorie'), fn ($q, $id) => $q->where('id_categorie', $id))
            ->when($request->integer('id_boutique'), fn ($q, $id) => $q->where('id_boutique', $id))
            ->when($request->string('recherche')->toString(), fn ($q, $recherche) => $q->where('nom', 'like', "%{$recherche}%"))
            ->orderByDesc('created_at')
            ->paginate($request->integer('par_page', 20));

        return response()->json($produits);
    }

    public function show(Produit $produit)
    {
        abort_if(! $produit->actif, 404);

        return response()->json($produit->load(['boutique', 'categorie']));
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->hasRole('super_admin'), 403);

        $donnees = $request->validate([
            'id_boutique' => ['required', 'integer', 'exists:boutiques_partenaires,id_boutique'],
            'id_categorie' => ['nullable', 'integer', 'exists:categories_produits,id_categorie'],
            'nom' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'prix' => ['required', 'numeric', 'min:0'],
            'image' => ['nullable', 'string', 'max:255'],
            'stock' => ['nullable', 'integer', 'min:0'],
        ]);

        $produit = Produit::create($donnees);

        return response()->json($produit, 201);
    }

    public function update(Request $request, Produit $produit)
    {
        abort_unless($request->user()->hasRole('super_admin'), 403);

        $donnees = $request->validate([
            'id_categorie' => ['nullable', 'integer', 'exists:categories_produits,id_categorie'],
            'nom' => ['sometimes', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'prix' => ['sometimes', 'numeric', 'min:0'],
            'image' => ['nullable', 'string', 'max:255'],
            'stock' => ['sometimes', 'integer', 'min:0'],
            'actif' => ['sometimes', 'boolean'],
        ]);

        $produit->update($donnees);

        return response()->json($produit);
    }

    public function destroy(Request $request, Produit $produit)
    {
        abort_unless($request->user()->hasRole('super_admin'), 403);
        $produit->delete();

        return response()->json(null, 204);
    }
}
