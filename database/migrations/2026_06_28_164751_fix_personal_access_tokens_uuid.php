<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personal_access_tokens', function (Blueprint $table) {
            // Vider la table avant de changer le type de colonne
            DB::table('personal_access_tokens')->truncate();

            $table->dropIndex(['tokenable_type', 'tokenable_id']);
            $table->string('tokenable_id', 36)->change();
        });
    }

    public function down(): void
    {
        Schema::table('personal_access_tokens', function (Blueprint $table) {
            DB::table('personal_access_tokens')->truncate();
            $table->dropIndex(['tokenable_type', 'tokenable_id']);
            $table->unsignedBigInteger('tokenable_id')->change();
        });
    }
};
