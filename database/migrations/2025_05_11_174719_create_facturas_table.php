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
        Schema::create('facturas', function (Blueprint $table) {
            $table->id();
            $table->string('nro_factura')->unique();
            $table->foreignId('user_id')->constrained('users');
            $table->decimal('monto', 10, 2);
            $table->string('concepto');
            $table->date('fecha');
            $table->string('periodoFacturacion')->nullable();
            $table->enum('estadoPago', ['pendiente', 'pagado', 'cancelado'])->default('pendiente');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facturas');
    }
};
