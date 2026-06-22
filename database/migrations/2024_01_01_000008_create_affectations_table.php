<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('affectations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('client_id');
            $table->uuid('mesure_id');
            $table->uuid('tailleur_id');
            $table->uuid('atelier_id');
            $table->uuid('createur_id')->nullable();
            $table->decimal('prix_tailleur', 12, 2)->nullable();
            $table->enum('statut', ['EN_ATTENTE', 'EN_COURS', 'TERMINE', 'VALIDE', 'ANNULE'])->default('EN_ATTENTE');
            $table->timestamp('date_creation')->useCurrent();
            $table->timestamp('date_echeance')->nullable();
            $table->timestamp('date_debut_reelle')->nullable();
            $table->timestamp('date_fin_reelle')->nullable();
            $table->timestamp('date_validation')->nullable();
            $table->timestamps();

            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
            $table->foreign('mesure_id')->references('id')->on('mesures')->onDelete('cascade');
            $table->foreign('tailleur_id')->references('id')->on('utilisateurs')->onDelete('cascade');
            $table->foreign('atelier_id')->references('id')->on('ateliers')->onDelete('cascade');
            $table->foreign('createur_id')->references('id')->on('utilisateurs')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('affectations');
    }
};
