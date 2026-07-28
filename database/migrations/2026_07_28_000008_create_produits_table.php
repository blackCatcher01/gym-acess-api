<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('produits', function (Blueprint $table) {
            $table->id('id_produit');
            $table->foreignId('id_boutique')->constrained('boutiques_partenaires', 'id_boutique')->cascadeOnDelete();
            $table->foreignId('id_categorie')->nullable()->constrained('categories_produits', 'id_categorie')->nullOnDelete();
            $table->string('nom', 150);
            $table->text('description')->nullable();
            $table->decimal('prix', 10, 2);
            $table->string('image', 255)->nullable();
            $table->unsignedInteger('stock')->default(0);
            $table->boolean('actif')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['id_boutique', 'actif']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produits');
    }
};
