<?php

namespace App\Http\Utils;

use App\Models\Business;
use App\Models\Module;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

trait BasicUtil
{
    function toggleActivation($modelClass, $disabledModelClass, $modelIdName, $modelId, $authUser)
    {
        // Fetch the model instance
        $modelInstance = $modelClass::where('id', $modelId)->first();
        if (!$modelInstance) {
            return response()->json([
                "message" => "No data found"
            ], 404);
        }

        $shouldUpdate = 0;
        $shouldDisable = 0;

        // Handle role-based permission
        if (empty($authUser->business_id)) {
            if ($authUser->hasRole('superadmin')) {
                if ($modelInstance->business_id !== NULL) {
                    return response()->json([
                        "message" => "You do not have permission to update this item due to role restrictions."
                    ], 403);
                } else {
                    $shouldUpdate = 1;
                }
            } else {
                if ($modelInstance->business_id !== NULL) {
                    return response()->json([
                        "message" => "You do not have permission to update this item due to role restrictions."
                    ], 403);
                } else if ($modelInstance->is_default == 0) {
                    if ($modelInstance->created_by != $authUser->id) {
                        return response()->json([
                            "message" => "You do not have permission to update this item due to role restrictions."
                        ], 403);
                    } else {
                        $shouldUpdate = 1;
                    }
                } else {
                    $shouldDisable = 1;
                }
            }
        } else {
            if ($modelInstance->business_id !== NULL) {
                if ($modelInstance->business_id != $authUser->business_id) {
                    return response()->json([
                        "message" => "You do not have permission to update this item due to role restrictions."
                    ], 403);
                } else {
                    $shouldUpdate = 1;
                }
            } else {
                if ($modelInstance->is_default == 0) {
                    if ($modelInstance->created_by != $authUser->id) {
                        return response()->json([
                            "message" => "You do not have permission to update this item due to role restrictions."
                        ], 403);
                    } else {
                        $shouldDisable = 1;
                    }
                } else {
                    $shouldDisable = 1;
                }
            }
        }

        // Perform the update action
        if ($shouldUpdate) {
            $modelInstance->update([
                'is_active' => !$modelInstance->is_active
            ]);
        }

        // Handle disabling the model
        if ($shouldDisable) {
            $disabledInstance = $disabledModelClass::where([
                $modelIdName => $modelInstance->id,
                'business_id' => $authUser->business_id,
                'created_by' => $authUser->id,
            ])->first();

            if (!$disabledInstance) {
                $disabledModelClass::create([
                    $modelIdName => $modelInstance->id,
                    'business_id' => $authUser->business_id,
                    'created_by' => $authUser->id,
                ]);
            } else {
                $disabledInstance->delete();
            }
        }
    }
    public function getLetterTemplateVariablesFunc()
    {
        $letterTemplateVariables = [
            'PERSONAL DETAILS',
            '[FULL_NAME]',
            '[NATIONALITY]',
            '[COURSE_FEE]',
            '[FEE_PAID]',
            '[PASSPORT_NUMBER]',
            '[DATE_OF_BIRTH]',
            '[STUDENT_ID]',
            '[COURSE_START_DATE]',
            '[COURSE_END_DATE]',
            '[LETTER_ISSUE_DATE]',
            '[STUDENT_STATUS]',

            '[EMAIL]',
            '[CONTACT_NUMBER]',
            '[SEX]',
            '[QR_CODE]',
            '[She/He]',
            '[Her/His]',
            '[Mr/Mrs]',

            'ADDRESS',
            '[ADDRESS]',
            '[CITY]',
            '[POSTCODE]',
            '[COUNTRY]',

            'COURSE DETAILS',
            '[AWARDING_BODY]',
            '[COURSE_TITLE]',
            '[COURSE_LEVEL]',
            '[COURSE_DURATION]',
            '[COURSE_DETAIL]',

            'COMPANY DETAILS',
            '[COMPANY_NAME]',
            '[COMPANY_ADDRESS_LINE_1]',
            '[COMPANY_CITY]',
            '[COMPANY_POSTCODE]',
            '[COMPANY_COUNTRY]',

            'PASSPORT DETAILS',
            '[PASSPORT_ISSUE_DATE]',
            '[PASSPORT_EXPIRY_DATE]',
            '[PLACE_OF_ISSUE]',




            'COMPANY DETAILS',
            'COMPANY_NAME',
            'COMPANY_ADDRESS_LINE_1',
            'COMPANY_CITY',
            'COMPANY_POSTCODE',
            'COMPANY_COUNTRY',






        ];

        return $letterTemplateVariables;
    }

    protected function getPrefix(?Model $relation): string
    {
        // First, check if the authenticated user's business has an identifier prefix
        $businessPrefix = optional($relation)->identifier_prefix;

        if ($businessPrefix) {
            return $businessPrefix;
        }

        // If no business identifier prefix is found, generate a prefix based on the relation's name
        if ($relation) {
            preg_match_all('/\b\w/', $relation->name, $matches);
            $initials = array_map(fn($match) => strtoupper($match[0]), $matches[0]);

            // Limit to the first two initials of each word, or as needed
            return substr(implode('', $initials), 0, 2 * count($matches[0]));
        }

        // If both are not found, return an empty string
        return '';
    }

    public function generateUniqueId(string $relationModel, int $relationModelId, string $mainModel, string $uniqueIdentifierColumn = 'unique_identifier'): string
    {
        // Fetch the related model instance
        $relation = $relationModel::find($relationModelId);

        // Generate the prefix based on the related model or the authenticated user's business
        $prefix = $this->getPrefix($relation);

        $currentNumber = 10001;

        // Generate a unique identifier by checking for existing records
        do {
            $uniqueIdentifier = $prefix . '-' . str_pad($currentNumber, 4, '0', STR_PAD_LEFT);
            $currentNumber++;
        } while ($this->identifierExists($mainModel, $uniqueIdentifierColumn, $uniqueIdentifier,$relationModelId));

        return $uniqueIdentifier;
    }


    protected function identifierExists(string $modelClass, string $column, string $value,$relationModelId): bool
    {
        return $modelClass::where($column, $value)
            ->where('business_id', $relationModelId)
            ->exists();
    }



    public function getLetterTemplateVariablesFuncV2()
    {
        $letterTemplateVariables = [
            'Personal_Details' => [
                '[FULL_NAME]',
                '[NATIONALITY]',
                '[COURSE_FEE]',
                '[FEE_PAID]',
                '[PASSPORT_NUMBER]',
                '[DATE_OF_BIRTH]',
                '[STUDENT_ID]',
                '[LETTER_ISSUE_DATE]',
                '[STUDENT_STATUS]',
                '[EMAIL]',
                '[CONTACT_NUMBER]',
                '[SEX]',
                '[QR_CODE]',
                '[She/He]',
                '[Her/His]',
                '[Mr/Mrs]',

            ],
            'Address' => [
                '[ADDRESS]',
                '[CITY]',
                '[POSTCODE]',
                '[COUNTRY]',
            ],
            'Course_Details' => [
                '[AWARDING_BODY]',
                '[COURSE_TITLE]',
                '[COURSE_LEVEL]',

                '[COURSE_DURATION]',
                '[COURSE_START_DATE]',
                '[COURSE_END_DATE]',
                '[COURSE_DETAIL]',
            ],
            'Company_Details' => [
                '[COMPANY_NAME]',
                '[COMPANY_ADDRESS_LINE_1]',
                '[COMPANY_CITY]',
                '[COMPANY_POSTCODE]',
                '[COMPANY_COUNTRY]',
            ],
            'Passport_Details' => [
                '[PASSPORT_ISSUE_DATE]',
                '[PASSPORT_EXPIRY_DATE]',
                '[PLACE_OF_ISSUE]',
            ],
        ];

        return $letterTemplateVariables;
    }

    // this function do all the task and returns transaction id or -1
    public function moveUploadedFilesBack($filePaths, $fileKey, $location, $arrayOfString = NULL)
    {


        if (is_array($arrayOfString)) {
            return collect($filePaths)->map(function ($filePathItem) use ($fileKey, $location) {
                $filePathItem[$fileKey] = $this->storeUploadedFiles($filePathItem[$fileKey], "", $location);
                return $filePathItem;
            });
        }


        // Get the temporary files location from the configuration
        $temporaryFilesLocation = config("setup-config.temporary_files_location");

        // Iterate over each file path in the array and perform necessary operations
        collect($filePaths)->each(function ($filePathItem) use ($temporaryFilesLocation, $fileKey, $location) {
            // Determine the file path based on whether a file key is provided
            $file = (!empty($fileKey)) ? $filePathItem[$fileKey] : $filePathItem;

            // Construct the full destination path and the temporary location path
            $destinationPath = public_path($file);
            $temporaryLocation = str_replace($location, $temporaryFilesLocation, $file);

            // Check if the file exists at the current location
            if (File::exists($destinationPath)) {
                try {
                    // Ensure the temporary directory exists
                    $temporaryDirectoryPath = dirname($temporaryLocation);
                    if (!File::exists($temporaryDirectoryPath)) {
                        File::makeDirectory($temporaryDirectoryPath, 0755, true);
                    }

                    // Attempt to move the file back to the temporary location
                    File::move($destinationPath, public_path($temporaryLocation));
                    Log::info("File moved back successfully from {$destinationPath} to {$temporaryLocation}");
                } catch (\Exception $e) {
                    // Log any exceptions that occur during the file move back
                    Log::error("Failed to move file back from {$destinationPath} to {$temporaryLocation}: " . $e->getMessage());
                }
            } else {
                // Log an error if the file does not exist at the current location
                Log::error("File does not exist at destination: {$destinationPath}");
            }
        });
    }

    public function storeUploadedFiles($filePaths, $fileKey, $location, $arrayOfString = NULL,$student_id=NULL)
    {


       // Step 1: Retrieve the business of the authenticated user
        $business = auth()->user()->business;

        // Add the business name to the location path
        $location = str_replace(' ', '_', $business->name) . "/" .(!empty($student_id)?("/". base64_encode($student_id) . "/"):""). $location;


      // Step 3: Handle nested arrays of file paths
        if (is_array($arrayOfString)) {
            return collect($filePaths)->map(function ($filePathItem) use ($fileKey, $location) {
                $filePathItem[$fileKey] = $this->storeUploadedFiles($filePathItem[$fileKey], "", $location);
                return $filePathItem;
            });
        }

        // Get the temporary files location from the configuration
        $temporaryFilesLocation = config("setup-config.temporary_files_location");


        // Iterate over each file path in the array and perform necessary operations
        return collect($filePaths)->map(function ($filePathItem) use ($temporaryFilesLocation, $fileKey, $location) {

            $file = !empty($fileKey) ? $filePathItem[$fileKey] : $filePathItem;


            // Construct the full temporary file path and the new location path
            $fullTemporaryPath = public_path($file);

            $newLocation = str_replace($temporaryFilesLocation, $location, $file);
            $newLocationPath = public_path($newLocation);

            // Check if the file exists at the temporary location
            if (File::exists($fullTemporaryPath)) {
                try {
                    // Ensure the destination directory exists
                    $newDirectoryPath = dirname($newLocationPath);
                    if (!File::exists($newDirectoryPath)) {
                        File::makeDirectory($newDirectoryPath, 0755, true);
                    }

                    // Attempt to move the file from the temporary location to the permanent location
                    File::move($fullTemporaryPath, $newLocationPath);
                    Log::info("File moved successfully from {$fullTemporaryPath} to {$newLocationPath}");
                } catch (Exception $e) {
                    throw new Exception(("Failed to move file from {$fullTemporaryPath} to {$newLocationPath}: " . $e->getMessage()), 500);
                }
            }

            // else {
            //     // Log an error if the file does not exist
            //     Log::error("File does not exist: {$fullTemporaryPath}");
            //     throw new Exception("File does not exist",500);
            // }

            // Update the file path in the item if a file key is provided
            if (!empty($fileKey)) {
                $filePathItem[$fileKey] = basename($newLocation);
            } else {
                // Otherwise, update the item with the new location
                $filePathItem = basename($newLocation);

            }

            return $filePathItem;
        })->toArray();
    }

}
