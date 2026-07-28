<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('abonnements', function (Blueprint $table) {
            // Nullable + genere a la creation de l'abonnement (voir
            // AbonnementController) — jamais dans $fillable, uniquement
            // via QrTokenService::generer() + forceFill().
            $table->string('qr_token', 255)->nullable()->unique()->after('statut');
        });
    }

    public function down(): void
    {
        Schema::table('abonnements', function (Blueprint $table) {
            $table->dropUnique(['qr_token']);
            $table->dropColumn('qr_token');
        });
    }
};
