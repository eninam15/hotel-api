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
        Schema::create('rankings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained('hoteles')->onDelete('cascade');
            $table->decimal('pt_general', 3, 2)->default(0.00);
            $table->integer('nro_valoraciones')->default(0);
            $table->integer('nro_reservas')->default(0);
            $table->integer('visitas')->default(0);
            $table->timestamps();
            
            // Un hotel solo debe tener un registro de ranking
            $table->unique('hotel_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rankings');
    }
};
