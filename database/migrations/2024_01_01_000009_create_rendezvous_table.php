<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rendezvous', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('client_id');
            $table->uuid('atelier_id');
            $table->uuid('mesure_id')->nullable();
            $table->timestamp('date_rdv');
            $table->string('type_rendezvous')->default('LIVRAISON');
            $table->text('notes')->nullable();
            $table->enum('statut', ['PLANIFIE', 'CONFIRME', 'ANNULE', 'TERMINE'])->default('PLANIFIE');
            $table->timestamps();

            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
            $table->foreign('atelier_id')->references('id')->on('ateliers')->onDelete('cascade');
            $table->foreign('mesure_id')->references('id')->on('mesures')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rendezvous');
    }
};
