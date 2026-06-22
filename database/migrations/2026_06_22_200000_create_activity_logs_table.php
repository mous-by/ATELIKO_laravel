<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('utilisateur_id')->nullable();
            $table->uuid('atelier_id')->nullable();
            $table->string('nom_utilisateur')->nullable();
            $table->string('role')->nullable();
            $table->string('action', 60); // LOGIN, LOGOUT, CREATE, UPDATE, DELETE, VIEW, PAYMENT
            $table->string('resource_type', 80)->nullable(); // CLIENT, MESURE, AFFECTATION...
            $table->string('resource_id', 36)->nullable();
            $table->string('description')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });

        Schema::table('utilisateurs', function (Blueprint $table) {
            $table->timestamp('last_seen_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
        Schema::table('utilisateurs', function (Blueprint $table) {
            $table->dropColumn('last_seen_at');
        });
    }
};
