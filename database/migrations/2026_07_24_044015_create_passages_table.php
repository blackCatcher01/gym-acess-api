<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('passages', function (Blueprint $table) {
            $table->id('id_passage');
            $table->foreignId('id_adherent')->constrained('adherents', 'id_adherent');
            $table->foreignId('id_salle')->constrained('salles', 'id_salle');
            $table->foreignId('id_cours')->nullable()->constrained('cours', 'id_cours');
            $table->dateTime('date_heure_passage')->index();
            $table->enum('resultat', ['autorise', 'refuse']);
            $table->string('motif_refus', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('passages');
    }
};