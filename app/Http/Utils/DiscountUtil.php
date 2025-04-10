<?php

namespace App\Http\Utils;

use App\Models\Coupon;
use App\Models\ServicePlanDiscountCode;
use Carbon\Carbon;
use Exception;

trait DiscountUtil
{
    // this function do all the task and returns transaction id or -1
    public function getDiscountAmount($request_data)
    {

        if (!empty($request_data["service_plan_id"]) && !empty($request_data["service_plan_discount_code"])) {
            $discount =  ServicePlanDiscountCode::where([
                "code" => $request_data["service_plan_discount_code"],
                "service_plan_id" => $request_data["service_plan_id"],
            ])
                ->first();
                if(!$discount){
                    throw new Exception("Invalid discount code",403);

                }


            return $discount->discount_amount;
        }

        return 0;


    }

    public function getCouponDiscount($business_id,$code,$amount)
    {

     $coupon =  Coupon::where([
        "business_id" => $business_id,
            "code" => $code,
            "is_active" => 1,

        ])

        // ->where('coupon_start_date', '<=', Carbon::now()->subDay())
        // ->where('coupon_end_date', '>=', Carbon::now()->subDay())
        ->first();

        if(!$coupon){
         return [
                "success" =>false,
                "message" => "no coupon is found",
            ];
        }


        if(!empty($coupon->min_total) && ($coupon->min_total > $amount )){
            return [
                "success" =>false,
                "message" => "minimim limit is " . $coupon->min_total,
            ];
        }
        if(!empty($coupon->max_total) && ($coupon->max_total < $amount)){
            return [
                "success" =>false,
                "message" => "maximum limit is " . $coupon->max_total,
            ];
        }

        if(!empty($coupon->redemptions) && $coupon->redemptions == $coupon->customer_redemptions){
            return [
                "success" =>false,
                "message" => "maximum people reached",
            ];
        }



        return [
            "success" =>true,
            "discount_type" => $coupon->discount_type,
            "discount_amount" => $coupon->discount_amount
        ];


    }


    function calculateFinalPrice($price, $discountAmount, $discountType)
    {
        if ($discountType === 'fixed') {
            // Calculate the final price with fixed discount
            $finalPrice = $price - $discountAmount;
        } elseif ($discountType === 'percentage') {
            // Calculate the discount amount
            $discountPercentage = $discountAmount / 100;
            $discountAmount = $price * $discountPercentage;

            // Calculate the final price with percentage discount
            $finalPrice = $price - $discountAmount;
        } else {
            // Invalid discount type
            return null;
        }

        // Round the final price to 2 decimal places
        $finalPrice = round($finalPrice, 2);

        return $finalPrice;
    }

    function calculateDiscountPriceAmount($price, $discountAmount, $discountType)
    {
        if ($discountType === 'fixed') {
            // Calculate the final price with fixed discount
            // $discount = $discountAmount;
        } elseif ($discountType === 'percentage') {
            // Calculate the discount amount
            $discountPercentage = $discountAmount / 100;
            $discountAmount = $price * $discountPercentage;

            // Calculate the final price with percentage discount
            // $discount =  $discountAmount;
        } else {
            // Invalid discount type
            return null;
        }

        // Round the final price to 2 decimal places
        $discount = round($discountAmount, 2);

        return $discount;
    }





}
