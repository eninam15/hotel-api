<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('servicios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->decimal('comision', 5, 2)->default(0);
            $table->timestamps();
        });

        // Tabla pivot para la relación muchos a muchos entre hoteles y servicios
        Schema::create('hotel_servicio', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained('hoteles')->onDelete('cascade');
            $table->foreignId('servicio_id')->constrained('servicios')->onDelete('cascade');
            $table->timestamps();
            
            // Índice compuesto para evitar duplicados
            $table->unique(['hotel_id', 'servicio_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_servicio');
        Schema::dropIfExists('servicios');
    }
};
