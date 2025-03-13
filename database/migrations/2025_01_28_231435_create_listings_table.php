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
        Schema::create('listings', function (Blueprint $table) {
            $table->id();

            $table->string('report_id')->references('id')->on('reports');

            $table->string('listing_id')->nullable();

            $table->timestamps();
            $table->decimal('rental_price', 10, 2)->nullable();

            $table->string('postcode', 8)->references('postcode')->on('postcodes');
            $table->string('subcode', 6)->references('subcode')->on('subcodes');
            $table->string('outcode', 4)->references('outcode')->on('outcodes');
            $table->string('district', 2)->references('district')->on('districts');

            $table->string('address')->nullable();


            $table->string('description')->nullable();

            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            $table->string('property_type')->nullable();
            $table->string('property_status')->nullable();
            $table->unsignedTinyInteger('bedrooms')->nullable();
            $table->unsignedTinyInteger('bathrooms')->nullable();

            $table->unsignedTinyInteger('student_friendly')->nullable();
            $table->unsignedTinyInteger('families_allowed')->nullable();
            $table->unsignedTinyInteger('pets_allowed')->nullable();
            $table->unsignedTinyInteger('smokers_allowed')->nullable();
            $table->unsignedTinyInteger('dss_covers_rent')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('listings');
    }
};
