<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    // Do NOT guard
    protected $guarded = [];

    /**
     * Each post belongs to a single user
     * 
     */

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
