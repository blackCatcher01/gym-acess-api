<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('utilisateur_centre_interet', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_utilisateur')->constrained('utilisateurs', 'id_utilisateur')->cascadeOnDelete();
            $table->foreignId('id_centre_interet')->constrained('centres_interet', 'id_centre_interet')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['id_utilisateur', 'id_centre_interet'], 'uci_utilisateur_centre_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('utilisateur_centre_interet');
    }
};