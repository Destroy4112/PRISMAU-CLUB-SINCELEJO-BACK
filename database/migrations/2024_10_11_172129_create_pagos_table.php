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
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mensualidad_id')->constrained('mensualidades')->onDelete('cascade');
            $table->string('email')->nullable();
            $table->string('identificacion')->nullable();
            $table->string('nombre')->nullable();
            $table->decimal('monto', 10, 2);
            $table->string('metodo_pago');
            $table->string('tarjeta')->nullable();
            $table->string('referencia_pago');
            $table->timestamp('fecha_pago');
            $table->string('soporte')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
