<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Models\BoutiquePartenaire;
use Illuminate\Http\Request;

class BoutiqueController extends Controller
{
    public function index(Request $request)
    {
        $boutiques = BoutiquePartenaire::query()
            ->where('actif', true)
            ->when($request->string('recherche')->toString(), fn ($q, $recherche) => $q->where('nom', 'like', "%{$recherche}%"))
            ->orderBy('nom')
            ->paginate($request->integer('par_page', 20));

        return response()->json($boutiques);
    }

    public function show(BoutiquePartenaire $boutique)
    {
        abort_if(! $boutique->actif, 404);

        return response()->json($boutique->load(['produits' => fn ($q) => $q->where('actif', true)]));
    }

    /**
     * Gestion des boutiques partenaires : ressource de plateforme (pas
     * rattachée à une salle), réservée au super_admin.
     */
    public function store(Request $request)
    {
        $this->autoriserSuperAdmin($request);

        $donnees = $request->validate([
            'nom' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'logo' => ['nullable', 'string', 'max:255'],
            'telephone_contact' => ['nullable', 'string', 'max:20'],
            'adresse' => ['nullable', 'string', 'max:255'],
            'ville' => ['nullable', 'string', 'max:100'],
        ]);

        $boutique = BoutiquePartenaire::create($donnees);

        return response()->json($boutique, 201);
    }

    public function update(Request $request, BoutiquePartenaire $boutique)
    {
        $this->autoriserSuperAdmin($request);

        $donnees = $request->validate([
            'nom' => ['sometimes', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'logo' => ['nullable', 'string', 'max:255'],
            'telephone_contact' => ['nullable', 'string', 'max:20'],
            'adresse' => ['nullable', 'string', 'max:255'],
            'ville' => ['nullable', 'string', 'max:100'],
            'actif' => ['sometimes', 'boolean'],
        ]);

        $boutique->update($donnees);

        return response()->json($boutique);
    }

    public function destroy(Request $request, BoutiquePartenaire $boutique)
    {
        $this->autoriserSuperAdmin($request);
        $boutique->delete();

        return response()->json(null, 204);
    }

    private function autoriserSuperAdmin(Request $request): void
    {
        abort_unless($request->user()->hasRole('super_admin'), 403, "Reserve a l'administrateur de la plateforme.");
    }
}
