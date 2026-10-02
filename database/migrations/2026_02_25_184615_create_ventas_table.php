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
        Schema::create('ventas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_id')->nullable()->constrained('pedidos')->nullOnDelete();
            $table->foreignId('empleado_id')->nullable()->constrained('empleados')->nullOnDelete();
            $table->string('metodo_pago')->default('EFECTIVO');
            $table->string('estado')->default('PAGADA');
            $table->unsignedBigInteger('subtotal')->default(0);
            $table->unsignedBigInteger('descuento')->default(0);
            $table->unsignedBigInteger('impuesto')->default(0);
            $table->unsignedBigInteger('total')->default(0);
            $table->string('referencia_pago')->nullable();
            $table->string('observacion')->nullable();
            $table->timestamps();
            $table->index(['pedido_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ventas');
    }
};
