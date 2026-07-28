<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('boutiques_partenaires', function (Blueprint $table) {
            $table->id('id_boutique');
            $table->string('nom', 150);
            $table->text('description')->nullable();
            $table->string('logo', 255)->nullable();
            $table->string('telephone_contact', 20)->nullable();
            $table->string('adresse', 255)->nullable();
            $table->string('ville', 100)->nullable();
            $table->boolean('actif')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('boutiques_partenaires');
    }
};
