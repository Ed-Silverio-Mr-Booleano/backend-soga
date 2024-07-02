<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Friendship extends Model
{
    use HasFactory;

    protected $fillable = [
        'userID1', 'userID2', 'status',
    ];

    public function user1()
    {
        return $this->belongsTo(User::class, 'userID1');
    }

    public function user2()
    {
        return $this->belongsTo(User::class, 'userID2');
    }
}
