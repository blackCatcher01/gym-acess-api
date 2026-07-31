<?php

namespace App\Http\Controllers;

use App\Models\Paiement;
use Illuminate\Http\Request;

class PaiementController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->hasAnyRole(['gerant', 'super_admin']), 403);
        $staff = $request->user()->staff;

        $paiements = Paiement::query()
            ->with(['abonnement.adherent.utilisateur', 'abonnement.formule.salle'])
            ->when(! $request->user()->hasRole('super_admin'), function ($q) use ($staff) {
                $q->whereHas('abonnement.formule', fn ($qq) => $qq->where('id_salle', $staff->id_salle));
            })
            ->when($request->string('statut_paiement')->toString(), fn ($q, $s) => $q->where('statut_paiement', $s))
            ->when($request->string('moyen_paiement')->toString(), fn ($q, $m) => $q->where('moyen_paiement', $m))
            ->orderByDesc('date_paiement')
            ->paginate($request->integer('par_page', 20));

        return response()->json($paiements);
    }
}