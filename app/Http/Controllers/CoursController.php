<?php

namespace App\Http\Controllers;

use App\Models\Cours;
use Illuminate\Http\Request;

class CoursController extends Controller
{
    /**
     * Cours consultables par tous les adherents, quelle que soit la
     * salle (plus de notion de "salle par defaut") — filtrable par
     * salle et par date pour affiner la recherche cote app.
     */
    public function index(Request $request)
    {
        $cours = Cours::query()
            ->with(['salle:id_salle,nom_salle,ville', 'coach.utilisateur:id_utilisateur,nom,prenom'])
            ->withCount(['reservations' => fn ($q) => $q->where('statut_reservation', 'confirmee')])
            ->when($request->integer('id_salle'), fn ($q, $id) => $q->where('id_salle', $id))
            ->when($request->date('date'), fn ($q, $date) => $q->whereDate('date_heure_debut', $date))
            ->where('date_heure_debut', '>=', now()->subHours(2))
            ->orderBy('date_heure_debut')
            ->paginate($request->integer('par_page', 20));

        return response()->json($cours);
    }

    public function show(Cours $cours)
    {
        $cours->load(['salle', 'coach.utilisateur'])
            ->loadCount(['reservations' => fn ($q) => $q->where('statut_reservation', 'confirmee')]);

        return response()->json($cours);
    }

    public function store(Request $request)
    {
        $this->verifierAutorisation($request, 'create', Cours::class);

        $staff = $request->user()->staff;

        $donnees = $request->validate([
            'nom_cours' => ['required', 'string', 'max:100'],
            'date_heure_debut' => ['required', 'date', 'after:now'],
            'duree_min' => ['required', 'integer', 'min:1'],
            'capacite_max' => ['required', 'integer', 'min:1'],
        ]);

        $cours = Cours::create([
            ...$donnees,
            'id_salle' => $staff->id_salle,
            'id_staff' => $staff->id_staff,
        ]);

        return response()->json($cours, 201);
    }

    public function update(Request $request, Cours $cours)
    {
        $this->verifierAutorisation($request, 'update', $cours);

        $donnees = $request->validate([
            'nom_cours' => ['sometimes', 'string', 'max:100'],
            'date_heure_debut' => ['sometimes', 'date'],
            'duree_min' => ['sometimes', 'integer', 'min:1'],
            'capacite_max' => ['sometimes', 'integer', 'min:1'],
        ]);

        $cours->update($donnees);

        return response()->json($cours);
    }

    public function destroy(Request $request, Cours $cours)
    {
        $this->verifierAutorisation($request, 'delete', $cours);
        $cours->delete();

        return response()->json(null, 204);
    }

    private function verifierAutorisation(Request $request, string $action, $sujet): void
    {
        if (! $request->user()->can($action, $sujet)) {
            abort(403);
        }
    }
}
