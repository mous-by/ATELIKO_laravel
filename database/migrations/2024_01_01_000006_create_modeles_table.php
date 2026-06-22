<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modeles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nom');
            $table->text('description')->nullable();
            $table->decimal('prix', 12, 2)->nullable();
            $table->string('photo_path')->nullable();
            $table->string('video_path')->nullable();
            $table->enum('categorie', ['ROBE', 'JUPE', 'HOMME', 'ENFANT', 'AUTRE'])->default('AUTRE');
            $table->boolean('est_actif')->default(true);
            $table->uuid('atelier_id');
            $table->timestamp('date_creation')->useCurrent();
            $table->timestamp('date_modification')->nullable();
            $table->timestamps();

            $table->foreign('atelier_id')->references('id')->on('ateliers')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modeles');
    }
};
