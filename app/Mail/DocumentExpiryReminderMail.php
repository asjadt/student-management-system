<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DocumentExpiryReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public $title;
    public $reminder;
    public $student;
    public $business;

    public function __construct($title,$reminder,  $student, $business)
    {
        $this->title = $title;
        $this->reminder = $reminder;
        $this->student = $student;
        $this->business = $business;
    }

    public function build()
    {
        $days_difference = now()->diffInDays($this->student->passport_expiry_date);
        return $this->subject($this->title)
            ->view('email.document_expiry_reminder')
            ->with([
                'title' => $this->title,
                'message_desc' =>  (($this->reminder->send_time == "after_expiry")
                ? ("The passport for the student " . $this->student->title . " ". $this->student->first_name . " " . $this->student->middle_name . " " . $this->student->last_name . " expired " . $days_difference . " days ago. Please renew it now.")
                :
                ("The passport for the student " . $this->student->title . " " . $this->student->first_name . " " . $this->student->middle_name . " " . $this->student->last_name . " will expire in " . $days_difference . " days. Please renew it in time.")),

                'student' => $this->student,
                'business' => $this->business
            ]);
    }
}
