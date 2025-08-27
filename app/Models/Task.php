<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = [
        'title',
        'description',
        'status',
        'user_id',      // the assigned user
        'created_by',   // the creator
    ];

    /**
     * User who is assigned the task.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * User who created the task.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function scopeVisibleTo($query, $user)
{
    if ($user->hasRole('Admin')) {
        return $query;
    }

    if ($user->hasRole('Manager')) {
        return $query->where(function ($q) use ($user) {
            $q->where('user_id', $user->id)
              ->orWhere('created_by', $user->id);
        });
    }

    return $query->where('user_id', $user->id);
}

}
