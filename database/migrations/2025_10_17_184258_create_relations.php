<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('variants', function (Blueprint $table) {
            // Change ai_texts_id to match ai_texts.id type
            $table->unsignedBigInteger('ai_texts_id')->nullable()->change();

            // Add the foreign key
            $table->foreign('ai_texts_id')
                ->references('id')
                ->on('ai_texts')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('variants', function (Blueprint $table) {
            $table->dropForeign(['ai_texts_id']);
            // Optional: revert column type back to int
            $table->integer('ai_texts_id')->nullable()->change();
        });
    }
};
