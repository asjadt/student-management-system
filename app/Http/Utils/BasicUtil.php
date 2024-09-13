<?php

namespace App\Http\Utils;

use App\Models\Business;
use App\Models\Module;

trait BasicUtil
{
    function toggleActivation($modelClass, $disabledModelClass, $modelIdName, $modelId, $authUser) {
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
    '[FIRST_NAME]',
    '[MIDDLE_NAME]',
    '[LAST_NAME]',
    '[NATIONALITY]',
    '[COURSE_FEE]',
    '[FEE_PAID]',
    '[PASSPORT_NUMBER]',
    '[DATE_OF_BIRTH]',
    '[COURSE_START_DATE]',
    '[LETTER_ISSUE_DATE]',
    '[STUDENT_STATUS]',

    '[EMAIL]',
    '[CONTACT_NUMBER]',
    '[SEX]',

    'ADDRESS',
    '[ADDRESS]',
    '[CITY]',
    '[POSTCODE]',
    '[COUNTRY]',

    'COURSE DETAILS',
    '[AWARDING_BODY]',
    '[COURSE_TITLE]',
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


    // this function do all the task and returns transaction id or -1

}
