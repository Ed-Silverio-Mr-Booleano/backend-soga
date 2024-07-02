<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Club extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome', 'descricao',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function contents()
    {
        return $this->hasMany(Content::class);
    }
}
