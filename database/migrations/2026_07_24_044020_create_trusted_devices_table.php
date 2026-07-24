<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('trusted_devices', function (Blueprint $table) {
            $table->id('id_device');
            $table->foreignId('id_utilisateur')->constrained('utilisateurs', 'id_utilisateur');
            $table->string('device_token', 255)->unique();
            $table->string('device_name', 150)->nullable();
            $table->dateTime('derniere_utilisation');
            $table->boolean('revoque')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trusted_devices');
    }
};