<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Picture extends Model
{
    use HasFactory;

    protected $guarded = array('id');

    protected $fillable = [
        'title',
        'post_comment',
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function tags()
    {
        return $this->belongsToMany('App\Models\Tag');
    }

    public function comments()
    {
        return $this->hasMany('App\Models\Comment');
    }

    public function usersWhoLike ()
    {
        return $this->belongsToMany('App\Models\User', 'likes', 'picture_id', 'user_id');
    }
}
