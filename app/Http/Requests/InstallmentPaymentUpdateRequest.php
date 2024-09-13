<?php




namespace App\Http\Requests;

use App\Models\InstallmentPayment;
use App\Rules\ValidateInstallmentPaymentName;
use Illuminate\Foundation\Http\FormRequest;

class InstallmentPaymentUpdateRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return  bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return  array
     */
    public function rules()
    {

        $rules = [

            'id' => [
                'required',
                'numeric',
                function ($attribute, $value, $fail) {

                    $installment_payment_query_params = [
                        "id" => $this->id,
                    ];
                    $installment_payment = InstallmentPayment::where($installment_payment_query_params)
                        ->first();
                    if (!$installment_payment) {
                        // $fail($attribute . " is invalid.");
                        $fail("no installment payment found");
                        return 0;
                    }
                    if (empty(auth()->user()->business_id)) {

                        if (auth()->user()->hasRole('superadmin')) {
                            if (($installment_payment->business_id != NULL)) {
                                // $fail($attribute . " is invalid.");
                                $fail("You do not have permission to update this installment payment due to role restrictions.");
                            }
                        } else {
                            if (($installment_payment->business_id != NULL || $installment_payment->is_default != 0 || $installment_payment->created_by != auth()->user()->id)) {
                                // $fail($attribute . " is invalid.");
                                $fail("You do not have permission to update this installment payment due to role restrictions.");
                            }
                        }
                    } else {
                        if (($installment_payment->business_id != auth()->user()->business_id || $installment_payment->is_default != 0)) {
                            // $fail($attribute . " is invalid.");
                            $fail("You do not have permission to update this installment payment due to role restrictions.");
                        }
                    }
                },
            ],

            'installment_plan_id' => [
                'required',
                'numeric',
                "exists:installment_plans,id"

            ],


            'amount_paid' => [
                'required',
                'numeric',







            ],

            'payment_date' => [
                'required',
                'date',


            ],

            'status' => [
                'required',
                'string',
                'in:pending,paid,overdue'
            ],
            'student_id' => [
                'required',
                'numeric',
                'exists:students,id'

            ],








        ];



        return $rules;
    }

    public function messages()
    {
        return [
            'status.required' => 'The status field is required.',
            'status.string' => 'The status field must be a string.',
            'status.in' => 'The status must be one of the following values: pending, paid, overdue.',
        ];
    }
}
