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
        Schema::create('familiars', function (Blueprint $table) {
            $table->id();
            $table->string('imagen')->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('asociado_id')->nullable();
            $table->unsignedBigInteger('adherente_id')->nullable();
            $table->string('Nombre');
            $table->string('Apellidos');
            $table->string('Correo')->nullable();
            $table->string('Telefono')->nullable();
            $table->string('FechaNacimiento')->nullable();
            $table->string('LugarNacimiento')->nullable();
            $table->string('TipoDocumento');
            $table->string('Documento')->unique();
            $table->string('Sexo');
            $table->string('Codigo')->nullable();
            $table->string('DireccionResidencia')->nullable();
            $table->string('CiudadResidencia')->nullable();
            $table->string('EstadoCivil')->nullable();
            $table->string('Cargo')->nullable();
            $table->string('Parentesco');
            $table->integer('Estado');
            $table->timestamps();
            $table->foreign('asociado_id')->references('id')->on('asociados')->onDelete('set null');
            $table->foreign('adherente_id')->references('id')->on('adherentes')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('familiars');
    }
};
