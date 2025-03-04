<?php

use App\Models\Family;
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
        Schema::create('families', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('codigo_proveedor')->nullable()->index();
            $table->string('marca_comercial', 100)->nullable()->index();
            $table->text('caracteristicas')->nullable();
            $table->string('nombre_familia')->nullable();
            $table->boolean('processed_with_ai')->default(false);
            $table->timestamps();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->integer('family_id')->nullable()->references('id')->on('families')->constrained();
        });

        /**
         * This is bullshit
         * Will be used for products with no family
         */
        Family::create([
            'codigo_proveedor' => '0000000000',
            'marca_comercial' => 'xxxxxxxxxxx',
            'caracteristicas' => 'xxxxxxxxxxx',
            'nombre_familia' => 'xxxxxxxxxxx',
            'processed_with_ai' => false,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('families');
    }
};
