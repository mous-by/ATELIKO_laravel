<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nom');
            $table->string('prenom');
            $table->string('contact', 20)->nullable();
            $table->string('adresse')->nullable();
            $table->string('email')->nullable();
            $table->string('photo')->nullable();
            $table->string('sexe')->nullable();
            $table->uuid('atelier_id');
            $table->timestamp('date_creation')->useCurrent();
            $table->timestamps();

            $table->foreign('atelier_id')->references('id')->on('ateliers')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
