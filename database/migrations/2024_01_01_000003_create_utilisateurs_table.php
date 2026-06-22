<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('utilisateurs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('prenom');
            $table->string('nom');
            $table->string('email')->unique();
            $table->string('telephone')->unique()->nullable();
            $table->string('mot_de_passe');
            $table->enum('role', ['SUPERADMIN', 'PROPRIETAIRE', 'SECRETAIRE', 'TAILLEUR'])->default('TAILLEUR');
            $table->boolean('actif')->default(true);
            $table->string('photo_path')->nullable();
            $table->uuid('atelier_id')->nullable();
            $table->rememberToken();
            $table->timestamps();

            $table->foreign('atelier_id')->references('id')->on('ateliers')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('utilisateurs');
    }
};
