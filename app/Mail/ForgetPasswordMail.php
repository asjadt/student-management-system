<?php

namespace App\Mail;

use App\Models\EmailTemplate;
use App\Models\EmailTemplateWrapper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ForgetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */



    private $user;
    private $client_site;





    public function __construct($user=null,$client_site = "")
    {

        $this->user = $user;

        $this->client_site = $client_site;

    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {


        $front_end_url = $this->client_site;
        return $this->view('email.forget_password',[
            "url" => ($front_end_url.'/auth/change-password?token='.$this->user->resetPasswordToken)
        ]);



    }
}
