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
        Schema::create('empleados', function (Blueprint $table) {
            $table->id();
            $table->string('imagen')->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('Nombre')->nullable();
            $table->string('Apellidos')->nullable();
            $table->string('Correo')->nullable();
            $table->string('Telefono')->nullable();
            $table->string('FechaNacimiento')->nullable();
            $table->string('LugarNacimiento')->nullable();
            $table->string('TipoDocumento')->nullable();
            $table->string('Documento')->unique();
            $table->string('Sexo')->nullable();
            $table->string('DireccionResidencia')->nullable();
            $table->string('CiudadResidencia')->nullable();
            $table->string('EstadoCivil')->nullable();
            $table->string('Cargo')->nullable();
            $table->integer('Estado');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empleados');
    }
};
