<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AbonnementPaiement extends Model
{
    protected $table = 'abonnement_paiement';

    protected $fillable = [
        'abonnement_id', 'reference', 'montant', 'devise', 'plan_code',
        'statut', 'provider', 'mode_paiement', 'transaction_ref',
        'owner_note', 'preuve_url', 'review_note', 'reviewed_by',
        'reviewed_at', 'paid_at'
    ];

    protected $casts = [
        'montant' => 'float',
        'reviewed_at' => 'datetime',
        'paid_at' => 'datetime'
    ];

    public function abonnement()
    {
        return $this->belongsTo(AbonnementAtelier::class, 'abonnement_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(Utilisateur::class, 'reviewed_by');
    }
}
