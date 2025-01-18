<?php

namespace App\Models\DBench;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends DBench
{
    protected $table = 'dbench_categories';

    public function posts()
    {
        return $this->belongsToMany(Post::class);
    }
}
