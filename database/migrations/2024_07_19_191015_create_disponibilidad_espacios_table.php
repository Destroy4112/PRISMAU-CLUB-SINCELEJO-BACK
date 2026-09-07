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
        Schema::create('disponibilidad_espacios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('espacio_id')->constrained()->onDelete('cascade');
            $table->string('Dia');
            $table->time('Inicio');
            $table->time('Fin');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disponibilidad_espacios');
    }
};
