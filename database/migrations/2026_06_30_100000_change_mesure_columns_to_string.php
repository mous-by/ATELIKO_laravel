<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $columns = [
        'epaule', 'manche', 'poitrine', 'taille', 'longueur', 'fesse',
        'tour_manche', 'longueur_poitrine', 'longueur_taille', 'longueur_fesse',
        'longueur_jupe', 'ceinture',
        'longueur_poitrine_robe', 'longueur_taille_robe', 'longueur_fesse_robe',
        'longueur_pantalon', 'cuisse', 'corps',
    ];

    public function up(): void
    {
        foreach ($this->columns as $col) {
            DB::statement("ALTER TABLE mesures MODIFY COLUMN `{$col}` VARCHAR(30) NULL");
        }
    }

    public function down(): void
    {
        foreach ($this->columns as $col) {
            DB::statement("ALTER TABLE mesures MODIFY COLUMN `{$col}` DECIMAL(6,2) NULL");
        }
    }
};
