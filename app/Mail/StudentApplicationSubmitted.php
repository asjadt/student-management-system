<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class StudentApplicationSubmitted extends Mailable
{
    use Queueable, SerializesModels;

    public $student;
    public $businessOwner;
    public $collegeName;

    /**
     * Create a new message instance.
     *
     * @param  \App\Models\Student  $student
     * @return void
     */
    public function __construct($student,$businessOwner,$collegeName)
    {
        $this->student = $student;
        $this->businessOwner = $businessOwner;
        $this->collegeName = $collegeName;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('New Student Application Reference: ' . $this->student->student_id)
            ->view('email.student_application')
            ->with([
                'studentName' => $this->student->title . " " . $this->student->first_name . ' ' . $this->student->middle_name . " " . $this->student->last_name,
                'applicationDate' => now()->format('d-m-Y'),
                'courseAppliedFor' => $this->student->course_title->name,  // Adjust if you have course relationships
                'applicationId' => $this->student->student_id,
                'studentEmail' => $this->student->email,
                'businessOwnerName' => $this->businessOwner->first_Name . ' ' . $this->businessOwner->middle_Name . " " . $this->businessOwner->last_Name,
                'collegeName' => $this->collegeName,
            ]);
    }




}
