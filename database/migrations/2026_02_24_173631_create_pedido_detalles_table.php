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
        Schema::create('pedido_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_id')->constrained('pedidos')->onDelete('cascade');
            $table->foreignId('producto_id')->constrained('productos');
            $table->unsignedInteger('cantidad')->default(1);
            $table->unsignedBigInteger('precio_unitario')->default(0);
            $table->unsignedBigInteger('subtotal')->default(0);
            $table->string('observaciones')->nullable();
            $table->enum('estado', ['Pendiente', 'En Preparacion', 'Preparado', 'Servido', 'Cerrado', 'Cancelado', 'Rechazado'])->default('Pendiente');
            $table->timestamps();
            $table->index(['pedido_id', 'estado']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedido_detalles');
    }
};
