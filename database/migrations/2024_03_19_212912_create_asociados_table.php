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
        Schema::create('asociados', function (Blueprint $table) {
            $table->id();
            $table->string('imagen')->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('Nombre');
            $table->string('Apellidos');
            $table->string('Correo')->unique();
            $table->string('Telefono');
            $table->string('FechaNacimiento')->nullable();
            $table->string('LugarNacimiento')->nullable();
            $table->string('TipoDocumento');
            $table->string('Documento')->unique();
            $table->string('Sexo');
            $table->string('Codigo')->unique()->nullable();
            $table->string('DireccionResidencia')->nullable();
            $table->string('CiudadResidencia')->nullable();
            $table->string('TiempoResidencia')->nullable();
            $table->string('EstadoCivil')->nullable();
            $table->string('Profesion')->nullable();
            $table->string('Trabajo')->nullable();
            $table->string('Cargo')->nullable();
            $table->string('TiempoServicio')->nullable();
            $table->string('TelOficina')->nullable();
            $table->string('DireccionOficina')->nullable();
            $table->string('CiudadOficina')->nullable();
            $table->integer('Estado');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asociados');
    }
};
