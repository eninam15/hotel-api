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
        Schema::create('administradores_hoteles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('operador_hotel_id')->constrained('operadores_hoteles')->onDelete('cascade');
            $table->timestamps();
            
            // Un usuario solo puede ser administrador de un operador una vez
            $table->unique(['user_id', 'operador_hotel_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('administradores_hoteles');
    }
};
