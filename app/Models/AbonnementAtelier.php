<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AbonnementAtelier extends Model
{
    protected $table = 'abonnement_atelier';
    
    protected $fillable = [
        'atelier_id', 'plan_id', 'statut', 'date_debut', 'date_fin', 
        'grace_end_at', 'auto_renew'
    ];

    protected $casts = [
        'date_debut' => 'datetime',
        'date_fin' => 'datetime',
        'grace_end_at' => 'datetime',
        'auto_renew' => 'boolean'
    ];

    public function atelier()
    {
        return $this->belongsTo(Atelier::class, 'atelier_id');
    }

    public function plan()
    {
        return $this->belongsTo(AbonnementPlan::class, 'plan_id');
    }

    public function paiements()
    {
        return $this->hasMany(AbonnementPaiement::class, 'abonnement_id');
    }
}
