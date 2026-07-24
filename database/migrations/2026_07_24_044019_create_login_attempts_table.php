<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('login_attempts', function (Blueprint $table) {
            $table->id('id_attempt');
            $table->string('telephone', 20);
            $table->string('ip_address', 45);
            $table->boolean('succes');
            $table->timestamp('created_at')->useCurrent();
            $table->index(['telephone', 'created_at']);
            $table->index(['ip_address', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('login_attempts');
    }
};