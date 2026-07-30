<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('utilisateurs', function (Blueprint $table) {
            $table->date('date_naissance')->nullable()->after('email');
            $table->enum('sexe', ['homme', 'femme', 'autre'])->nullable()->after('date_naissance');
            $table->string('comment_connu', 100)->nullable()->after('sexe');
            $table->boolean('profil_complete')->default(false)->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('utilisateurs', function (Blueprint $table) {
            $table->dropColumn(['date_naissance', 'sexe', 'comment_connu', 'profil_complete']);
        });
    }
};