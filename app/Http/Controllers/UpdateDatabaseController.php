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
                        // Get the file name from the current path
                        $original_file_name = $student_doc_object["file_name"];
                        $file_name_only = basename($original_file_name); // Extract just the file name

                        // Generate the new file path (business folder and student ID)
                        $business_folder = str_replace(' ', '_', $student->business->name);
                        $encoded_student_id = base64_encode($student->id);
                        $new_file_path = "$business_folder/$encoded_student_id/student_docs/" . $file_name_only;

                        // Copy the file to the new location (keep the original file, and copy it to the new location)
                        if (Storage::exists($original_file_name)) {
                            // Ensure the target directory exists
                            Storage::makeDirectory(dirname($new_file_path));

                            // Copy the file to the new location
                            Storage::copy($original_file_name, $new_file_path);
                        }

                        // Update the file name in the document object to store only the file name (no path)
                        $student_doc_object["file_name"] = $file_name_only;
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
