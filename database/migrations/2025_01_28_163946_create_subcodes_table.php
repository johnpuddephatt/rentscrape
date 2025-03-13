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
        Schema::create('subcodes', function (Blueprint $table) {
            $table->string('subcode', 6)->primary();
            $table->string('outcode', 4)->references('outcode')->on('outcodes');
            $table->string('district', 2)->references('district')->on('districts');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subcodes');
    }
};
