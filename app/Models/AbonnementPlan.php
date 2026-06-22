<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AbonnementPlan extends Model
{
    protected $table = 'abonnement_plan';
    protected $fillable = ['code', 'libelle', 'duree_mois', 'prix', 'devise', 'actif'];
    protected $casts = ['actif' => 'boolean', 'prix' => 'float'];
}
