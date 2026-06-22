<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Rendezvous extends Model
{
    use HasUuids;

    protected $table = 'rendezvous';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'client_id', 'atelier_id', 'mesure_id',
        'date_rdv', 'type_rendezvous', 'notes', 'statut',
    ];

    protected $casts = [
        'date_rdv' => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function atelier()
    {
        return $this->belongsTo(Atelier::class, 'atelier_id');
    }

    public function mesure()
    {
        return $this->belongsTo(Mesure::class, 'mesure_id');
    }
}
