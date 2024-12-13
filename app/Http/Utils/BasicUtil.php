<?php

namespace App\Http\Utils;

use App\Models\Business;
use App\Models\Module;
use Illuminate\Database\Eloquent\Model;

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

}
