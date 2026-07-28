<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('centres_interet', function (Blueprint $table) {
            $table->id('id_centre_interet');
            $table->string('nom', 100);
            $table->string('icone', 50)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('centres_interet');
    }
};
