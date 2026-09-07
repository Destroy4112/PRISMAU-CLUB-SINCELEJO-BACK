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
        Schema::create('noticias', function (Blueprint $table) {
            $table->id();
            $table->string('Titulo');
            $table->text('Descripcion');
            $table->string('Imagen')->nullable();
            $table->date('Vencimiento');
            $table->date('Fecha');
            $table->time('Hora');
            $table->string('Tipo');
            $table->string('Destinatario');
            $table->boolean('Correo')->nullable(true);
            $table->boolean('Push')->nullable(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('noticias');
    }
};
