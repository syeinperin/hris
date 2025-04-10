<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewEmployeeAccountMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;           // The newly created user instance
    public $plainPassword;  // The plain password to be sent via email

    /**
     * Create a new message instance.
     *
     * @param User $user
     * @param string $plainPassword
     */
    public function __construct(User $user, $plainPassword)
    {
        $this->user = $user;
        $this->plainPassword = $plainPassword;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Your New Employee Account')
                    ->view('emails.new_employee_account');
    }
}
