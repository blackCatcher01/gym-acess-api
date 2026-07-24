<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Services\OtpDelivery\InfobipOtpSender;

class SendOtpJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(InfobipOtpSender $sender): void
    {
        $sender->envoyer($this->telephone, $this->code, $this->canal);
    }
}
