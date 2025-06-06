<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContractEndingNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $mailData;

    /**
     * Create a new message instance.
     *
     * $mailData = [
     *   'upcoming' => Collection of Employee, 
     *   'expired'  => Collection of Employee,
     *   'days'     => int,
     *   'today'    => string Y-m-d,
     * ];
     */
    public function __construct(array $mailData)
    {
        $this->mailData = $mailData;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this
            ->subject("Employees with Contracts Ending Within {$this->mailData['days']} Days")
            ->view('emails.contract_ending_notification')
            ->with($this->mailData);
    }
}
