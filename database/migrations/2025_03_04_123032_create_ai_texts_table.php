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
        Schema::create('ai_texts', function (Blueprint $table) {
            $table->id();
            $table->text('meta_titulo')->nullable();
            $table->text('meta_descripcion')->nullable();
            $table->longText('descripcion_corta')->nullable();
            $table->longText('descripcion_larga')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_texts');
    }
};
