<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    protected $fillable = [
    'name', 'email', 'phone', 'source', 'status',
    'assigned_to', 'created_by', 'assigned_by' // ← add this!
];


    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    // Optional alias (used in index)
    public function assignedToUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
    public function assignedBy()
{
    return $this->belongsTo(User::class, 'assigned_by');
}
public function assignedTo()
{
    return $this->belongsTo(User::class, 'assigned_to');
}


}
