<?php

namespace App\Jobs;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class VerificationEmailJob implements ShouldQueue
{
    use Queueable;
    public $event;
    /**
     * Create a new job instance.
     */
    public function __construct($event)
    {
        $this->event = $event;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->event->user instanceof MustVerifyEmail && ! $this->event->user->hasVerifiedEmail()) {
            $this->event->user->sendEmailVerificationNotification();
        }
    }
}
