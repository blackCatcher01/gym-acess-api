<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('adherents', function (Blueprint $table) {
            $table->dropForeign(['id_salle']);
            $table->dropUnique(['qr_token']);
            $table->dropColumn(['id_salle', 'qr_token']);
        });
    }

    public function down(): void
    {
        Schema::table('adherents', function (Blueprint $table) {
            $table->foreignId('id_salle')->nullable()->after('id_adherent')->constrained('salles', 'id_salle');
            $table->string('qr_token', 255)->nullable()->unique()->after('id_salle');
        });
    }
};
