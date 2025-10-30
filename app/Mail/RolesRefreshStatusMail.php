<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RolesRefreshStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public $status;  // 'success' or 'failed'
    public $bodyMessage; // message text

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($status, $bodyMessage)
    {
        $this->status = $status;
        $this->bodyMessage = $bodyMessage;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->subject("Roles Refresh Job")
            ->view('email.roles_refresh_status')
            ->with([
                'status' => $this->status,
                'bodyMessage' => $this->bodyMessage,
            ]);
    }
}
