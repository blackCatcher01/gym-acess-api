<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('auth_otps', function (Blueprint $table) {
            $table->id('id_otp');
            $table->foreignId('id_utilisateur')->nullable()->constrained('utilisateurs', 'id_utilisateur');
            $table->string('telephone', 20)->index();
            $table->string('otp_hash', 255);
            $table->enum('purpose', ['login', 'reset', 'verify_phone']);
            $table->enum('statut', ['emitted', 'consumed', 'expired', 'failed'])->index();
            $table->dateTime('expires_at');
            $table->timestamps();
            $table->index(['telephone', 'statut', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auth_otps');
    }
};