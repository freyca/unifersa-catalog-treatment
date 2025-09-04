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
        Schema::table('products', function (Blueprint $table) {
            $table->string('descripcion_formato_venta')->nullable()->defalult(null)->after('nombre_variante');
            $table->string('unidades_formato_venta')->nullable()->defalult(null)->after('nombre_variante');
            $table->string('ancho_especial')->nullable()->defalult(null)->after('nombre_variante');
            $table->string('logistica_especial')->nullable()->defalult(null)->after('nombre_variante');
            $table->string('agreement_dangerous_road_especial')->nullable()->defalult(null)->after('nombre_variante');
            $table->string('tipo_iva')->nullable()->defalult(null)->after('nombre_variante');
            $table->string('unidad_facturacion')->nullable()->defalult(null)->after('nombre_variante');
            $table->string('unidad_minima_venta')->nullable()->defalult(null)->after('nombre_variante');
            $table->string('peso_unidad_minima_venta')->nullable()->defalult(null)->after('nombre_variante');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('unidad_facturacion');
            $table->dropColumn('descripcion_formato_venta');
            $table->dropColumn('unidades_formato_venta');
            $table->dropColumn('unidad_minima_venta');
            $table->dropColumn('ancho_especial');
            $table->dropColumn('agreement_dangerous_road_especial');
            $table->dropColumn('logistica_especial');
            $table->dropColumn('peso_unidad_minima_venta');
            $table->dropColumn('tipo_iva');
        });
    }
};
