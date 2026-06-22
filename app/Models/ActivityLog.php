<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    public $timestamps = true;
    const UPDATED_AT = null;

    protected $fillable = [
        'utilisateur_id', 'atelier_id', 'nom_utilisateur', 'role',
        'action', 'resource_type', 'resource_id', 'description', 'ip_address',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class, 'utilisateur_id');
    }

    public static function record(string $action, array $extra = []): void
    {
        $user = auth()->user();
        static::create(array_merge([
            'utilisateur_id' => $user?->id,
            'atelier_id'     => $user?->atelier_id,
            'nom_utilisateur'=> $user ? trim($user->prenom . ' ' . $user->nom) : null,
            'role'           => $user?->role,
            'action'         => $action,
            'ip_address'     => request()->ip(),
        ], $extra));
    }
}
