<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UpdateDatabaseController extends Controller
{
    public function updatePreviousEducationHistory()
    {
        $students = Student::all(); // Get all students

        foreach ($students as $student) {
            // Decode previous education history if it's not already an array
            if (!is_array($student->previous_education_history)) {
                $previous_education_history = json_decode($student->previous_education_history, true);
            } else {
                $previous_education_history = $student->previous_education_history;
            }

            // Check if 'student_docs' exists and is an array
            if (isset($previous_education_history['student_docs']) && is_array($previous_education_history['student_docs'])) {
                foreach ($previous_education_history['student_docs'] as &$student_doc_object) {
                    // Ensure each student_doc_object has a file_name property
                    if (isset($student_doc_object["file_name"])) {
                        // Generate the new file path
                        $business_folder = str_replace(' ', '_', $student->business->name);
                        $encoded_student_id = base64_encode($student->id);

                        $fileName = basename($student_doc_object["file_name"]);

                        $new_file_path = "$business_folder/$encoded_student_id/student_docs/" . $fileName;



                        // Move the file to the new folder
                        if (Storage::exists($student_doc_object["file_name"])) {
                            Storage::move($student_doc_object["file_name"], $new_file_path);
                        }

                        // Update the file_name in the document object to store only the file name
                        $student_doc_object["file_name"] = $fileName;
                    }
                }
            }

            // Save the updated previous_education_history back to the student
            $student->previous_education_history = $previous_education_history;
            $student->save();
        }

        return 'Previous education history updated successfully!';
    }
}
