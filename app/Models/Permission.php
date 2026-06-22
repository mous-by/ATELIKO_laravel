<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Permission extends Model
{
    use HasUuids;

    protected $table = 'permissions';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['code', 'description'];

    public function utilisateurs()
    {
        return $this->belongsToMany(Utilisateur::class, 'utilisateur_permissions', 'permission_id', 'utilisateur_id');
    }
}
