<?php

namespace App\Models;

use App\Mail\NewUserWelcomeMail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Mail;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'username',
        'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        // Fired event when the model is a user is created
        static::created(function ($user){
            $user->profile()->create([
                'title' => $user->username,
            ]);
            Mail::to($user->email)->send(new NewUserWelcomeMail());
        });
    }

    /**
     * User profile's one to one relationship
     * 
     */

    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    /**
     * User profile's one to many relationship
     * 
     */

    public function posts()
    {
        // In a one to many relationship, use a plural
        return $this->hasMany(Post::class)->orderBy('created_at','DESC');
    }

    public function following()
    {
        return $this->belongsToMany(Profile::class);
    }
}
