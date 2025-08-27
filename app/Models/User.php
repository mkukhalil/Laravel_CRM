<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
     use Notifiable, HasRoles;

    protected $fillable = [
        'name', 'email', 'password',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function createdLeads() {
        return $this->hasMany(Lead::class, 'created_by');
    }

    public function assignedLeads() {
        return $this->hasMany(Lead::class, 'assigned_to');
    }
    public function tasks()
{
    return $this->hasMany(Task::class);
}

}
