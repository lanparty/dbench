<?php

namespace App\Models\DBench;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class User extends DBench
{
    protected $table = 'dbench_users';
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function profile()
    {
        return $this->hasOne(Profile::class);
    }
}
