<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Employee;

class ProbationRegularizedNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $employee;

    /**
     * Construct with the Employee model whose probation just ended.
     */
    public function __construct(Employee $employee)
    {
        $this->employee = $employee;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this
            ->subject("Congratulations! You Are Now a Regular Employee")
            ->view('emails.probation_regularized')
            ->with([
                'name' => $this->employee->name,
                'code' => $this->employee->employee_code,
                'date' => now()->toDateString(),
            ]);
    }
}
