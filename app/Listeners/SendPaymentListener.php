<?php

namespace App\Listeners;

use App\Events\SuccessfulPayment;
use App\Jobs\SendPaymentJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendPaymentListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(SuccessfulPayment $event): void
    {
        // ارسال ایمیل به کاربر
        SendPaymentJob::dispatch($event->payment);
    }
}
