<?php

namespace App\Mail;

use App\Http\Utils\BasicEmailUtil;
use App\Models\Business;
use App\Models\EmailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendPasswordMail extends Mailable
{
    use Queueable, SerializesModels, BasicEmailUtil;

    public $user;
    public $password;

    public function __construct($user, $password)
    {
        $this->user = $user;
        $this->password = $password;
    }

    public function build()
    {



        $front_end_url = env('FRONT_END_URL');
        $password_reset_link = $front_end_url . '/auth/change-password?token=' . $this->user->resetPasswordToken;

        // Directly pass variables to the email view
        $subject = "Your Password from " . ($this->user->business ? ($this->user->business->name . " HRM") : env("APP_NAME"));

        return $this->subject($subject)
            ->view('email.send_password_mail', [
                "full_name" => $this->user->title . " " . $this->user->first_Name . " " . $this->user->middle_Name . " " . $this->user->last_Name,
                "app_name" => env('APP_NAME'),
                "password" => $this->password,
                "password_reset_link" => $password_reset_link,

            ]);
    }
}
