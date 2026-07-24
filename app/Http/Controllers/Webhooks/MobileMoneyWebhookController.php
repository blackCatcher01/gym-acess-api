<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\Paiement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MobileMoneyWebhookController extends Controller
{
    public function handle(Request $request, string $operateur)
    {
        $reference = $request->input('reference_transaction');
        $statut = $request->input('statut'); // succes | echec, selon operateur

        if (! $reference) {
            return response()->json(['message' => 'reference_transaction manquante.'], 422);
        }

        // Idempotency : si déjà traité, on répond 200 sans rejouer la logique métier
        // (évite le double traitement si l'opérateur renvoie le webhook plusieurs fois).
        $paiement = Paiement::where('reference_transaction', $reference)->first();

        if (! $paiement) {
            Log::warning("Webhook $operateur recu pour une reference inconnue", ['reference' => $reference]);
            return response()->json(['message' => 'Paiement inconnu.'], 404);
        }

        if ($paiement->statut_paiement === 'confirme') {
            return response()->json(['message' => 'Deja traite.']);
        }

        DB::transaction(function () use ($paiement, $statut) {
            $paiement->update([
                'statut_paiement' => $statut === 'succes' ? 'confirme' : 'echoue',
            ]);

            if ($statut === 'succes') {
                $paiement->abonnement->update(['statut' => 'actif']);
            }
        });

        return response()->json(['message' => 'OK']);
    }
}