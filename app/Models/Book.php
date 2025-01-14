<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;
    protected $fillable = ['title', 'author', 'description', 'user_id'];



    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function Payments()
    {
        return $this->hasMany(Payment::class);
    }

}
