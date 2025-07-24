<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class StudentApplicationAcknowledgement extends Mailable
{
    use Queueable, SerializesModels;

   public $student;
    public $collegeName;
    public $collegeEmail;

    public function __construct($student, $collegeName, $collegeEmail)
    {
        $this->student = $student;
        $this->collegeName = $collegeName;
        $this->collegeEmail = $collegeEmail;
    }

    public function build()
    {
        return $this->subject('Your Student Application Has Been Submitted - ' . $this->student->student_id)
            ->view('email.student_acknowledgement')
            ->with([
                'studentName' => $this->student->title . " " . $this->student->first_name . ' ' . $this->student->middle_name . " " . $this->student->last_name,
                'applicationId' => $this->student->student_id,
                'collegeName' => $this->collegeName,
                'collegeEmail' => $this->collegeEmail,
            ]);
    }
}
