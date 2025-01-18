<?php

namespace App\Models\DBench;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends DBench
{
    protected $table = 'dbench_tags';

    public function posts()
    {
        return $this->belongsToMany(Post::class);
    }
}
