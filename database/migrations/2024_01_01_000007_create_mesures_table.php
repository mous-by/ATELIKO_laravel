<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mesures', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('client_id');
            $table->uuid('atelier_id');
            $table->date('date_mesure')->nullable();
            $table->string('type_vetement')->nullable();
            $table->string('sexe')->nullable();
            $table->decimal('prix', 12, 2)->nullable();
            $table->text('description')->nullable();
            $table->boolean('affecte')->default(false);
            $table->uuid('modele_reference_id')->nullable();
            $table->string('modele_nom')->nullable();
            $table->string('photo_path')->nullable();
            $table->string('habit_photo_path')->nullable();
            $table->string('audio_description_path')->nullable();

            // Mesures communes
            $table->decimal('epaule', 6, 2)->nullable();
            $table->decimal('manche', 6, 2)->nullable();
            $table->decimal('poitrine', 6, 2)->nullable();
            $table->decimal('taille', 6, 2)->nullable();
            $table->decimal('longueur', 6, 2)->nullable();
            $table->decimal('fesse', 6, 2)->nullable();
            $table->decimal('tour_manche', 6, 2)->nullable();
            $table->decimal('longueur_poitrine', 6, 2)->nullable();
            $table->decimal('longueur_taille', 6, 2)->nullable();
            $table->decimal('longueur_fesse', 6, 2)->nullable();

            // Mesures jupe
            $table->decimal('longueur_jupe', 6, 2)->nullable();
            $table->decimal('ceinture', 6, 2)->nullable();

            // Mesures robe
            $table->decimal('longueur_poitrine_robe', 6, 2)->nullable();
            $table->decimal('longueur_taille_robe', 6, 2)->nullable();
            $table->decimal('longueur_fesse_robe', 6, 2)->nullable();

            // Mesures homme
            $table->decimal('longueur_pantalon', 6, 2)->nullable();
            $table->decimal('cuisse', 6, 2)->nullable();
            $table->decimal('corps', 6, 2)->nullable();

            $table->timestamps();

            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
            $table->foreign('atelier_id')->references('id')->on('ateliers')->onDelete('cascade');
            $table->foreign('modele_reference_id')->references('id')->on('modeles')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mesures');
    }
};
