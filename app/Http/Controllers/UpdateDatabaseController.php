<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

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

                        $new_file_path = public_path("$business_folder/$encoded_student_id/student_docs/" . $fileName);


                        $file_name = $student_doc_object["file_name"]; // e.g., "student_files/1735372025_Image.jpg"
                        $file_path = public_path(ltrim($file_name, '/')); // Full path to

                        // Check with File::exists
                        if (File::exists($file_path)) {
                            echo "File exists (File facade): " . $file_path . "<br>";

                            // Ensure the destination directory exists using mkdir
                            $destinationDirectory = dirname($new_file_path); // Get the directory path from the file path
                            if (!is_dir($destinationDirectory)) {
                                mkdir($destinationDirectory, 0755, true); // Create the directory with permissions and allow recursive creation
                                echo "Directory created: " . $destinationDirectory . "<br>";
                            }

                            // Copy the file
                            File::copy($file_path, $new_file_path);
                            echo "File copied successfully (File facade) to: " . $new_file_path . "<br>";
                        } else {
                            echo "File does not exist (File facade): " . $file_path . "<br>";
                        }



                        // Update the file_name in the document object to store only the file name
                        $student_doc_object["file_name"] = $fileName;
                    }
                }
            }

            // Save the updated previous_education_history back to the student
            $student->previous_education_history = $previous_education_history;

            echo json_encode($student->previous_education_history) . "<br>";
            $student->save();
        }

        return 'Previous education history updated successfully!';
    }

    public function updateBusinessLogo()
    {
        $businesses = Business::withTrashed()->get(); // Get all students

        foreach ($businesses as $business) {



                    // Ensure each student_doc_object has a file_name property
                    if (!empty($business->logo)) {
                        // Generate the new file path
                        $business_folder = str_replace(' ', '_', $business->name);
                     ;

                        $fileName = basename($business->logo);

                        $new_file_path = public_path("/",$business_folder ."/".config("setup-config.business_gallery_location")."/" . $fileName);


                        $file_name = $business->logo; // e.g., "student_files/1735372025_Image.jpg"
                        $file_path = public_path(ltrim($file_name, '/')); // Full path to

                        // Check with File::exists
                        if (File::exists($file_path)) {
                            echo "File exists (File facade): " . $file_path . "<br>";

                            // Ensure the destination directory exists using mkdir
                            $destinationDirectory = dirname($new_file_path); // Get the directory path from the file path
                            if (!is_dir($destinationDirectory)) {
                                mkdir($destinationDirectory, 0755, true); // Create the directory with permissions and allow recursive creation
                                echo "Directory created: " . $destinationDirectory . "<br>";
                            }

                            // Copy the file
                            File::copy($file_path, $new_file_path);
                            echo "File copied successfully (File facade) to: " . $new_file_path . "<br>";
                        } else {
                            echo "File does not exist (File facade): " . $file_path . "<br>";
                        }



                        // Update the file_name in the document object to store only the file name
                        $business->logo = $fileName;
                    }



            // Save the updated previous_education_history back to the student


            echo json_encode($business->logo) . "<br>";
            // $business->save();
        }

        return 'Business logo updated successfully!';
    }
}
