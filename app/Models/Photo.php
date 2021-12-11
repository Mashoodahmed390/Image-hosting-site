<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\Model;

class Photo extends Model
{
    use HasFactory;
    protected $fillable = [
        'path',
        'shareablelink',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
