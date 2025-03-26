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
        Schema::create('variants', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_producto')->nullable();
            $table->string('modelo_producto')->nullable();
            $table->string('codigo_proveedor')->nullable();
            $table->string('marca_comercial')->nullable();
            $table->json('codigos_articulos')->nullable();
            $table->json('variantes')->nullable();
            $table->integer('ai_texts_id')->nullable()->references('id')->on('ai_texts')->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('variants');
    }
};
