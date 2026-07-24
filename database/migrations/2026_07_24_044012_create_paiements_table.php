<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('paiements', function (Blueprint $table) {
            $table->id('id_paiement');
            $table->foreignId('id_abonnement')->constrained('abonnements', 'id_abonnement');
            $table->decimal('montant', 10, 2);
            $table->enum('moyen_paiement', ['wave', 'orange_money', 'free_money', 'especes']);
            $table->string('reference_transaction', 100)->nullable()->unique();
            $table->enum('statut_paiement', ['en_attente', 'confirme', 'echoue'])->default('en_attente')->index();
            $table->dateTime('date_paiement');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paiements');
    }
};