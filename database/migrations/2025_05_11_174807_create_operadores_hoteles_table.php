<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('operadores_hoteles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('hotel_id')->constrained('hoteles')->onDelete('cascade');
            $table->string('nombreHotel')->nullable(); // Nombre alternativo del hotel para este operador
            $table->timestamps();
            
            // Un usuario solo puede ser operador de un hotel una vez
            $table->unique(['user_id', 'hotel_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operadores_hoteles');
    }
};
