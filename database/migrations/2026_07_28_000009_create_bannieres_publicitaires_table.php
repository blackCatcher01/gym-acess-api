<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bannieres_publicitaires', function (Blueprint $table) {
            $table->id('id_banniere');
            $table->string('titre', 150);
            $table->string('image', 255);
            $table->string('lien_url', 255)->nullable();
            $table->unsignedSmallInteger('ordre_affichage')->default(0);
            $table->boolean('actif')->default(true);
            $table->date('date_debut')->nullable();
            $table->date('date_fin')->nullable();
            $table->timestamps();

            $table->index(['actif', 'ordre_affichage']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bannieres_publicitaires');
    }
};
