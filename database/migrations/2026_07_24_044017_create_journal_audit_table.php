<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('journal_audit', function (Blueprint $table) {
            $table->id('id_audit');
            $table->foreignId('id_staff')->constrained('staff', 'id_staff');
            $table->foreignId('id_salle')->constrained('salles', 'id_salle');
            $table->string('action', 150);
            $table->dateTime('date_action')->index();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->text('details')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_audit');
    }
};