<?php

namespace App\Repositories;

use App\Models\Payment;
use Illuminate\Support\Facades\Auth;

class PaymentRepository
{
    public function createPayment($amount, $trackId,$book_id)

    {
        return Payment::create([
            'user_id' => Auth::id(),
            'payment_method' => 'zibal',
            'amount' => $amount,
            'trackId' => $trackId,
            'payment_status' => 'pending',
            'payment_date' => now(),
            'book_id' => $book_id
        ]);
    }
}
