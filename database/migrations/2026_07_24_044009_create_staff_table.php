<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('staff', function (Blueprint $table) {
            $table->foreignId('id_staff')
                ->primary()
                ->constrained('utilisateurs', 'id_utilisateur')
                ->cascadeOnDelete();
            $table->foreignId('id_salle')->constrained('salles', 'id_salle');
            $table->enum('role_staff', ['coach', 'gerant', 'super_admin']);
            $table->date('date_embauche')->nullable();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff');
    }
};