<?php

namespace App\Jobs;

use App\Mail\PaymentMail;
use App\Models\Payment;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendPaymentJob implements ShouldQueue
{
    use Queueable;
    public $payment;
    /**
     * Create a new job instance.
     */
    public function __construct($payment)
    {
        $this->payment = $payment;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // ارسال ایمیل به کاربر
        Mail::to($this->payment->user->email)->send(new PaymentMail($this->payment));
    }
}
