<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Profile extends Model
{
    use HasFactory;
    // Disable protection
    protected $guarded = [];
    /**
     * Profile's one to one relationship with user
     * 
     */

    public function user()
    {
        return $this->BelongsTo(User::class);
    }

    // Get the profile image
    public function profileImage()
    {
        $imagePath = ($this->image) ? $this->image : "http://127.0.0.1:8000/storage/profile/bUnSucoES6fm3zoYypRQ5NlkINHbJf95OdmySyzp.png";
        return '/storage/' . $imagePath;
    }

    public function followers()
    {
        return $this->belongsToMany(User::class);
    }
}
