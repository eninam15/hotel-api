<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Añadir campos para la información del cliente
            $table->date('fec_nacimiento')->nullable();
            $table->string('pais')->nullable();
            $table->string('telefono')->nullable();
            $table->string('foto_perfil')->nullable();
            $table->string('direccion')->nullable();
            $table->enum('sexo', ['M', 'F', 'O'])->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'fec_nacimiento',
                'pais',
                'telefono',
                'foto_perfil',
                'direccion',
                'sexo'
            ]);
        });
    }
};