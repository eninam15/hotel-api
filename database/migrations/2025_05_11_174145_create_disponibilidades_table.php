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
        Schema::create('disponibilidades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('habitacion_id')->constrained('habitaciones')->onDelete('cascade');
            $table->date('fecha');
            $table->integer('disponibles');
            $table->integer('reservadas')->default(0);
            $table->decimal('precio', 10, 2)->nullable();
            $table->timestamps();
            
            // Índice compuesto para búsquedas rápidas por fecha y habitación
            $table->unique(['habitacion_id', 'fecha']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disponibilidades');
    }
};
