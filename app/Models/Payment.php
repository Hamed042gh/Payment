<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

   protected $fillable = [
        'user_id',
        'amount',
        'payment_method',
        'payment_status',
        'payment_date',
        'transaction_id',
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
