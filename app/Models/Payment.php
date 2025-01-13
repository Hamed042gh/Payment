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
        'trackId',
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }

     // Accessor for payment_status
     public function getPaymentStatusTextAttribute()
     {
         $statusTexts = [
             0 => 'Pending',
             1 => 'Success',
             2 => 'Failed',
             3 => 'Cancelled',
             4 => 'Processing',
         ];
 
         return $statusTexts[$this->payment_status] ?? 'Unknown';
     }
}
