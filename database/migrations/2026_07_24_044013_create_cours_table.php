<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cours', function (Blueprint $table) {
            $table->id('id_cours');
            $table->foreignId('id_salle')->constrained('salles', 'id_salle');
            $table->foreignId('id_staff')->nullable()->constrained('staff', 'id_staff');
            $table->string('nom_cours', 100);
            $table->dateTime('date_heure_debut')->index();
            $table->integer('duree_min');
            $table->integer('capacite_max');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cours');
    }
};