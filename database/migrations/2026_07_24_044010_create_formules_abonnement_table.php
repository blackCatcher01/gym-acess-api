<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('formules_abonnement', function (Blueprint $table) {
            $table->id('id_formule');
            $table->foreignId('id_salle')->constrained('salles', 'id_salle');
            $table->string('nom_formule', 100);
            $table->integer('duree_jours');
            $table->decimal('prix', 10, 2);
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('formules_abonnement');
    }
};