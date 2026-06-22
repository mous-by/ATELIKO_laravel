<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE paiements MODIFY moyen ENUM('ESPECES','MOBILE_MONEY','VIREMENT','CARTE') NOT NULL DEFAULT 'ESPECES'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE paiements MODIFY moyen ENUM('ESPECES','MOBILE_MONEY') NOT NULL DEFAULT 'ESPECES'");
        }
    }
};
