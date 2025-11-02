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
        Schema::create('launchpads', function (Blueprint $table) {
            $table->id();
            $table->string('spacex_id')->unique(); // ID de l'API SpaceX
            $table->string('name');
            $table->text('full_name')->nullable();
            $table->string('locality')->nullable();
            $table->string('region')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->integer('launch_attempts')->default(0);
            $table->integer('launch_successes')->default(0);
            $table->string('status')->nullable(); // active, inactive, unknown, retired, lost, under construction
            $table->text('details')->nullable();
            $table->json('images')->nullable(); // URLs des images
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('launchpads');
    }
};
