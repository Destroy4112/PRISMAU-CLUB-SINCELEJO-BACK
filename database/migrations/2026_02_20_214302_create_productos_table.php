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
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 180);
            $table->text('descripcion')->nullable();
            $table->string('imagen')->nullable();
            $table->enum('tipo', ['COMIDA', 'BEBIDA'])->index();
            $table->string('categoria', 100)->nullable();
            $table->decimal('precio', 12, 2)->default(0);
            $table->foreignId('cocina_id')->nullable()->constrained('cocinas');
            $table->foreignId('insumo_presentacion_id')->nullable()->constrained('insumo_presentaciones');
            $table->decimal('cantidad_por_plato', 12, 3)->default(1);
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->unique(['nombre', 'tipo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
