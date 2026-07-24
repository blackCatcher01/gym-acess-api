<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('adherents', function (Blueprint $table) {
            $table->foreignId('id_adherent')
                ->primary()
                ->constrained('utilisateurs', 'id_utilisateur')
                ->cascadeOnDelete();
            $table->foreignId('id_salle')->constrained('salles', 'id_salle');
            $table->string('qr_token', 255)->unique();
            $table->date('date_inscription');
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adherents');
    }
};