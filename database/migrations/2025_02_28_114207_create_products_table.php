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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->boolean('descatalogado')->default(false)->index();
            $table->string('ean13')->nullable()->index();
            $table->string('codigo_articulo')->nullable()->index();
            $table->string('referencia_proveedor')->nullable()->index();
            $table->string('codigo_proveedor')->nullable()->index();
            $table->string('descripcion')->nullable();
            $table->string('marca_comercial')->nullable();
            $table->string('precio_venta')->nullable();
            $table->string('stock')->nullable();
            $table->string('familia')->nullable();
            $table->string('imagen')->nullable();
            $table->text('caracteristicas')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('processed_products_with_ai');
    }
};
