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
        Schema::create('rockets', function (Blueprint $table) {
            $table->id();
            $table->string('spacex_id')->unique(); // ID de l'API SpaceX
            $table->string('name');
            $table->string('type')->nullable();
            $table->boolean('active')->default(true);
            $table->integer('stages')->nullable();
            $table->integer('boosters')->nullable();
            $table->decimal('cost_per_launch', 15, 2)->nullable(); // coût en USD
            $table->decimal('success_rate_pct', 5, 2)->nullable(); // pourcentage de succès
            $table->date('first_flight')->nullable();
            $table->string('country')->nullable();
            $table->string('company')->nullable();
            $table->string('wikipedia')->nullable();
            $table->text('description')->nullable();
            $table->json('flickr_images')->nullable(); // URLs des images
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rockets');
    }
};
