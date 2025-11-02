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
        Schema::create('launches', function (Blueprint $table) {
            $table->id();
            $table->string('spacex_id')->unique(); // ID de l'API SpaceX
            $table->string('flight_number')->nullable();
            $table->string('name');
            $table->timestamp('date_utc')->nullable();
            $table->timestamp('date_local')->nullable();
            $table->boolean('success')->nullable();
            $table->json('failures')->nullable(); // détails des échecs
            $table->boolean('upcoming')->default(false);
            $table->text('details')->nullable();
            
            // Rocket et Launchpad info (stockées directement)
            $table->string('rocket_spacex_id')->nullable();
            $table->string('rocket_name')->nullable();
            $table->string('launchpad_spacex_id')->nullable();
            $table->string('launchpad_name')->nullable();
            
            // Liens externes
            $table->json('links')->nullable(); // patch, reddit, flickr, presskit, webcast, youtube_id, article_link, wikipedia
            
            // Charges utiles
            $table->json('payloads')->nullable();
            
            // Équipage (pour les missions habitées)
            $table->json('crew')->nullable();
            
            // Cores (boosters)
            $table->json('cores')->nullable();
            
            $table->timestamps();
            
            // Index pour améliorer les performances
            $table->index(['date_utc', 'success']);
            $table->index('upcoming');
            $table->index('rocket_spacex_id');
            $table->index('launchpad_spacex_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('launches');
    }
};
