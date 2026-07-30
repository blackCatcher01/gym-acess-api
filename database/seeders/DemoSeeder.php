<?php

namespace Database\Seeders;

use App\Models\Adherent;
use App\Models\BannierePublicitaire;
use App\Models\BoutiquePartenaire;
use App\Models\CategorieProduit;
use App\Models\FormuleAbonnement;
use App\Models\Produit;
use App\Models\Salle;
use App\Models\Staff;
use App\Models\Utilisateur;
use Illuminate\Database\Seeder;

/**
 * Donnees de demonstration UNIQUEMENT — ne s'execute jamais en
 * production (voir garde dans run()), pour ne pas polluer une vraie
 * base avec des comptes de test.
 *
 * Comptes crees (connexion via OTP, code de test 000000 — voir
 * OtpController) :
 *   - Super admin  : +2250700000001
 *   - Gerant       : +2250700000002 (rattache a "Access Gym Cocody")
 *   - Coach        : +2250700000003 (idem)
 *   - Adherent     : +2250700000010 (profil complet, abonnement actif)
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command?->warn('DemoSeeder ignore en production.');
            return;
        }

        $salle = Salle::firstOrCreate(
            ['nom_salle' => 'Access Gym Cocody'],
            ['adresse' => 'Rue des Jardins, Cocody', 'ville' => 'Abidjan', 'telephone_contact' => '+2252722000000']
        );

        $formuleMensuelle = FormuleAbonnement::firstOrCreate(
            ['id_salle' => $salle->id_salle, 'nom_formule' => 'Mensuel'],
            ['duree_jours' => 30, 'prix' => 15000, 'actif' => true]
        );
        FormuleAbonnement::firstOrCreate(
            ['id_salle' => $salle->id_salle, 'nom_formule' => 'Trimestriel'],
            ['duree_jours' => 90, 'prix' => 40000, 'actif' => true]
        );
        FormuleAbonnement::firstOrCreate(
            ['id_salle' => $salle->id_salle, 'nom_formule' => 'Annuel'],
            ['duree_jours' => 365, 'prix' => 150000, 'actif' => true]
        );

        // -- Super admin --
        $admin = Utilisateur::firstOrCreate(
            ['telephone' => '+2250700000001'],
            ['nom' => 'Admin', 'prenom' => 'Super', 'type_utilisateur' => 'staff', 'is_active' => true]
        );
        $admin->forceFill(['profil_complete' => true])->save();
        if (! $admin->hasRole('super_admin')) {
            $admin->assignRole('super_admin');
        }

        // -- Gerant, rattache a la salle --
        $gerant = Utilisateur::firstOrCreate(
            ['telephone' => '+2250700000002'],
            ['nom' => 'Sylla', 'prenom' => 'Aminata', 'type_utilisateur' => 'staff', 'is_active' => true]
        );
        $gerant->forceFill(['profil_complete' => true])->save();
        if (! $gerant->hasRole('gerant')) {
            $gerant->assignRole('gerant');
        }
        if (! $gerant->staff) {
            Staff::creerPourUtilisateur($gerant, ['id_salle' => $salle->id_salle, 'role_staff' => 'gerant', 'date_embauche' => now()]);
        }

        // -- Coach, rattache a la salle --
        $coach = Utilisateur::firstOrCreate(
            ['telephone' => '+2250700000003'],
            ['nom' => 'Ouattara', 'prenom' => 'Kader', 'type_utilisateur' => 'staff', 'is_active' => true]
        );
        $coach->forceFill(['profil_complete' => true])->save();
        if (! $coach->hasRole('coach')) {
            $coach->assignRole('coach');
        }
        if (! $coach->staff) {
            Staff::creerPourUtilisateur($coach, ['id_salle' => $salle->id_salle, 'role_staff' => 'coach', 'date_embauche' => now()]);
        }

        // -- Adherent de demo, avec un abonnement actif --
        $adherentUser = Utilisateur::firstOrCreate(
            ['telephone' => '+2250700000010'],
            ['nom' => 'Koné', 'prenom' => 'Fatou', 'date_naissance' => '1998-04-12', 'sexe' => 'femme', 'type_utilisateur' => 'adherent', 'is_active' => true]
        );
        $adherentUser->forceFill(['profil_complete' => true])->save();
        $adherent = $adherentUser->adherent ?: Adherent::creerPourUtilisateur($adherentUser);

        if (! $adherent->abonnements()->where('statut', 'actif')->exists()) {
            $adherent->abonnements()->create([
                'id_formule' => $formuleMensuelle->id_formule,
                'date_debut' => now(),
                'date_fin' => now()->addDays(30),
                'statut' => 'actif',
            ]);
        }

        // -- Marketplace de demo --
        $boutique = BoutiquePartenaire::firstOrCreate(
            ['nom' => 'SportShop CI'],
            ['ville' => 'Abidjan', 'telephone_contact' => '+2250700001111', 'actif' => true]
        );
        $categorie = CategorieProduit::firstOrCreate(['nom' => 'Vêtements de sport'], ['slug' => 'vetements-de-sport']);
        Produit::firstOrCreate(
            ['id_boutique' => $boutique->id_boutique, 'nom' => 'Legging sport'],
            ['id_categorie' => $categorie->id_categorie, 'prix' => 8000, 'stock' => 25, 'actif' => true]
        );

        // -- Bannieres de demo --
        BannierePublicitaire::firstOrCreate(
            ['titre' => 'Promo rentrée -20%'],
            ['image' => 'https://placehold.co/600x200', 'ordre_affichage' => 1, 'actif' => true]
        );

        $this->command?->info('Donnees de demonstration creees (comptes de test : voir doc-block de DemoSeeder).');
    }
}