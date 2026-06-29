<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

class Utilisateur extends Authenticatable
{
    use HasApiTokens, HasUuids, Notifiable;

    protected $table = 'utilisateurs';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'prenom', 'nom', 'email', 'telephone', 'mot_de_passe',
        'role', 'actif', 'photo_path', 'atelier_id',
    ];

    protected $hidden = ['mot_de_passe', 'remember_token'];

    protected $casts = [
        'actif' => 'boolean',
    ];

    public function getAuthPassword()
    {
        return $this->mot_de_passe;
    }

    protected function photoUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->photo_path ? Storage::disk('public')->url($this->photo_path) : null,
        );
    }

    public function atelier()
    {
        return $this->belongsTo(Atelier::class, 'atelier_id');
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'utilisateur_permissions', 'utilisateur_id', 'permission_id');
    }

    public function affectations()
    {
        return $this->hasMany(Affectation::class, 'tailleur_id');
    }

    public function notifications_ateliko()
    {
        return $this->hasMany(NotificationAteliko::class, 'recipient_id');
    }

    public function hasPermission(string $code): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->permissions->contains('code', $code);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'SUPERADMIN';
    }

    public function isProprietaire(): bool
    {
        return $this->role === 'PROPRIETAIRE';
    }

    public function isSecretaire(): bool
    {
        return $this->role === 'SECRETAIRE';
    }

    public function isTailleur(): bool
    {
        return $this->role === 'TAILLEUR';
    }
}
