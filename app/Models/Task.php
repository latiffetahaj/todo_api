<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    //
    protected $fillable = ['user_id', 'name', 'description', 'is_completed', 'priority', 'completed_at'];



    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
