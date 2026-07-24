<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id('id_reservation');
            $table->foreignId('id_adherent')->constrained('adherents', 'id_adherent');
            $table->foreignId('id_cours')->constrained('cours', 'id_cours');
            $table->enum('statut_reservation', ['confirmee', 'liste_attente', 'annulee']);
            $table->dateTime('date_reservation');
            $table->timestamps();
            $table->unique(['id_adherent', 'id_cours']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};