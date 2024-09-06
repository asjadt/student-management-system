<?php

namespace App\Http\Utils;

use App\Models\Business;
use App\Models\Module;

trait BasicUtil
{
    public function getLetterTemplateVariablesFunc()
    {
        $letterTemplateVariables = [
            'PERSONAL DETAILS',
            '[FULL_NAME]',
            '[NI_NUMBER]',
            '[DATE_OF_BIRTH]',
            '[GENDER]',
            '[PHONE]',
            '[EMAIL]',
            'EMPLOYMENT DETAILS',
            '[DESIGNATION]',
            '[EMPLOYMENT_STATUS]',
            '[JOINING_DATE]',
            '[SALARY_PER_ANNUM]',
            '[WEEKLY_CONTRACTUAL_HOURS]',
            '[MINIMUM_WORKING_DAYS_PER_WEEK]',
            '[OVERTIME_RATE]',
            'ADDRESS',
            '[ADDRESS_LINE_1]',
            // '[ADDRESS_LINE_2]',
            '[CITY]',
            '[POSTCODE]',
            '[COUNTRY]',
            'BANK DETAILS',
            '[SORT_CODE]',
            '[ACCOUNT_NUMBER]',
            '[ACCOUNT_NAME]',
            '[BANK_NAME]',

            'COMPANY DETAILS',
            'COMPANY_NAME',
            'COMPANY_ADDRESS_LINE_1',
            'COMPANY_CITY',
            'COMPANY_POSTCODE',
            'COMPANY_COUNTRY',


            'TERMINATION DETAILS',
            'TERMINATION_DATE',
            'REASON_FOR_TERMINATION',
            'TERMINATION_TYPE',



            'TYPE_OF_LEAVE',
            'LEAVE_START_DATE',
            'LEAVE_END_DATE',
            'TOTAL_DAYS'

        ];

        return $letterTemplateVariables;
    }
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

    // this function do all the task and returns transaction id or -1

}
