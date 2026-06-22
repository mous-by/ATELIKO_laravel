<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paiements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->decimal('montant', 12, 2);
            $table->enum('moyen', ['ESPECES', 'MOBILE_MONEY'])->default('ESPECES');
            $table->string('reference')->unique()->nullable();
            $table->timestamp('date_paiement')->useCurrent();
            $table->enum('type_paiement', ['CLIENT', 'TAILLEUR'])->default('CLIENT');
            $table->uuid('client_id')->nullable();
            $table->uuid('tailleur_id')->nullable();
            $table->uuid('atelier_id');
            $table->text('note')->nullable();
            $table->timestamps();

            $table->foreign('client_id')->references('id')->on('clients')->onDelete('set null');
            $table->foreign('tailleur_id')->references('id')->on('utilisateurs')->onDelete('set null');
            $table->foreign('atelier_id')->references('id')->on('ateliers')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paiements');
    }
};
