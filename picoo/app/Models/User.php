<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'followers_count',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function pictures()
    {
        return $this->hasMany('App\Models\Picture');
    }

    public function favorites ()
    {
        return $this->belongsToMany('App\Models\Picture', 'likes', 'user_id', 'picture_id');
    }

    public function followers ()
    {
        return $this->belongsToMany('App\Models\User', 'follower_user', 'user_id', 'follower_id');
    }

    public function follows ()
    {
        return $this->belongsToMany('App\Models\User', 'follower_user', 'follower_id', 'user_id');
    }

    public function ngUsers ()
    {
        return $this->belongsToMany('App\Models\User', 'ng_user', 'user_id', 'ng_user_id');
    }
}
