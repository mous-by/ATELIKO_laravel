<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('abonnement_plan', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('libelle');
            $table->unsignedInteger('duree_mois');
            $table->decimal('prix', 12, 2);
            $table->string('devise', 10)->default('XOF');
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });
        Schema::create('abonnement_atelier', function (Blueprint $table) {
            $table->id();
            $table->uuid('atelier_id');
            $table->foreignId('plan_id')->constrained('abonnement_plan');
            $table->string('statut', 30)->default('ACTIVE');
            $table->timestamp('date_debut')->nullable();
            $table->timestamp('date_fin')->nullable();
            $table->timestamp('grace_end_at')->nullable();
            $table->boolean('auto_renew')->default(false);
            $table->timestamps();
            $table->foreign('atelier_id')->references('id')->on('ateliers')->cascadeOnDelete();
        });
        Schema::create('abonnement_paiement', function (Blueprint $table) {
            $table->id();
            $table->foreignId('abonnement_id')->constrained('abonnement_atelier')->cascadeOnDelete();
            $table->string('reference')->unique();
            $table->decimal('montant', 12, 2);
            $table->string('devise', 10)->default('XOF');
            $table->string('plan_code', 50)->nullable();
            $table->string('statut', 30)->default('PENDING');
            $table->string('provider', 40)->nullable();
            $table->string('mode_paiement', 40)->nullable();
            $table->string('transaction_ref', 120)->nullable();
            $table->text('owner_note')->nullable();
            $table->string('preuve_url')->nullable();
            $table->text('review_note')->nullable();
            $table->uuid('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        DB::table('abonnement_plan')->insert([
            ['code'=>'MENSUEL','libelle'=>'Mensuel','duree_mois'=>1,'prix'=>0,'devise'=>'XOF','actif'=>true,'created_at'=>now(),'updated_at'=>now()],
            ['code'=>'TRIMESTRIEL','libelle'=>'Trimestriel','duree_mois'=>3,'prix'=>0,'devise'=>'XOF','actif'=>true,'created_at'=>now(),'updated_at'=>now()],
            ['code'=>'SEMESTRIEL','libelle'=>'Semestriel','duree_mois'=>6,'prix'=>0,'devise'=>'XOF','actif'=>true,'created_at'=>now(),'updated_at'=>now()],
            ['code'=>'ANNUEL','libelle'=>'Annuel','duree_mois'=>12,'prix'=>0,'devise'=>'XOF','actif'=>true,'created_at'=>now(),'updated_at'=>now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('abonnement_paiement');
        Schema::dropIfExists('abonnement_atelier');
        Schema::dropIfExists('abonnement_plan');
    }
};
