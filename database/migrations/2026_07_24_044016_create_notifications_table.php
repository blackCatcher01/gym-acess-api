<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id('id_notification');
            $table->foreignId('id_utilisateur')->constrained('utilisateurs', 'id_utilisateur');
            $table->foreignId('id_abonnement')->nullable()->constrained('abonnements', 'id_abonnement');
            $table->enum('type_notification', ['rappel_cours', 'relance_abonnement', 'confirmation_paiement', 'autre']);
            $table->enum('canal', ['push', 'whatsapp', 'sms']);
            $table->text('contenu');
            $table->dateTime('date_envoi');
            $table->enum('statut_envoi', ['envoye', 'echoue', 'en_attente']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};