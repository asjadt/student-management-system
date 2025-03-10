<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Business extends Model
{

    use HasFactory,  SoftDeletes;

    protected $connection = 'default';

    protected $appends = ['is_subscribed'];
    protected $fillable = [
        "trail_end_date",

        "student_disabled_fields",
        "student_optional_fields",

        "name",
        "url",
        "color_theme_name",
        "about",
        "web_page",
        "phone",
        "email",
        "additional_information",
        "address_line_1",
        "address_line_2",
        "lat",
        "long",
        "country",
        "city",
        "currency",
        "postcode",
        "logo",
        "image",
        "background_image",
        "status",
        "is_active",
        "business_tier_id",
        "owner_id",
        'created_by',

        "service_plan_id",
        "service_plan_discount_code",
        "service_plan_discount_amount",

        "letter_template_header",
        "letter_template_footer"

    ];

    protected $casts = [
        'student_disabled_fields' => 'array',
        'student_optional_fields' => 'array'
    ];

    public function owner(){
        return $this->belongsTo(User::class,'owner_id', 'id');
    }

    public function business_tier(){
        return $this->belongsTo(businessTier::class,'business_tier_id', 'id');
    }


    public function times(){
        return $this->hasMany(BusinessTime::class,'business_id', 'id');
    }

    public function getCalculatedNumberOfEmployeesAllowedAttribute()
    {

        if (!empty($this->number_of_employees_allowed)) {
            return 0;
        }
        $service_plan = $this->service_plan;

        return !empty($service_plan)
            ? $service_plan->number_of_employees_allowed
            : 0;
    }

    public function service_plan()
    {
        return $this->belongsTo(ServicePlan::class, 'service_plan_id', 'id');
    }

    public function current_subscription()
    {
        return $this->hasOne(BusinessSubscription::class, 'business_id', 'id')
            ->where('business_subscriptions.service_plan_id', $this->service_plan_id)
            ->orderByDesc("business_subscriptions.id")
        ;
    }


    public function students(){
        return $this->hasMany(Student::class,'business_id', 'id');
    }

    private function isTrailDateValid($trail_end_date)
    {
        // Return false if trail_end_date is empty or null
        if (empty($trail_end_date)) {
            return false;
        }

        // Parse the date and check validity
        $parsedDate = Carbon::parse($trail_end_date);
        return !($parsedDate->isPast() && !$parsedDate->isToday());
    }

    public function getIsSubscribedAttribute($value)
    {

        $user = auth()->user();
        if (empty($user)) {
            return 0;
        }

        // Return 0 if the business is not active
        if (!$this->is_active) {
            return 0;
        }

        // Check for self-registered businesses
        // if ($this->is_self_registered_businesses) {
        //     $validTrailDate = $this->isTrailDateValid($this->trail_end_date);
        //     $latest_subscription = $this->current_subscription;

        //     // If no valid subscription and no valid trail date, return 0
        //     if (!$this->isValidSubscription($latest_subscription) && !$validTrailDate) {
        //         return 0;
        //     }
        // } else {
        //     // For non-self-registered businesses
        //     // If the trail date is empty or invalid, return 0
        //     if (!$this->isTrailDateValid($this->trail_end_date)) {
        //         return 0;
        //     }
        // }


        if (!$this->isTrailDateValid($this->trail_end_date)) {
            return 0;
        }

        return 1;
    }

    public function scopeActiveStatus($query, $is_active)
    {
        return $query->when($is_active !== null, function ($query) use ($is_active) {
            $query->where(function ($subQuery) use ($is_active) {
                if ($is_active) {
                    // For active or subscribed businesses
                    $subQuery->where('is_active', 1)
                        ->where(function ($q) {
                            $q->where(function ($innerQuery) {
                                $innerQuery->where('is_self_registered_businesses', 0)
                                    ->whereNotNull('trail_end_date')
                                    ->where(function ($trailEndQuery) {
                                        $trailEndQuery->where('trail_end_date', '>', now())
                                            ->orWhere('trail_end_date', now()->toDateString());
                                    });
                            })
                                ->orWhere(function ($q) {
                                    $q->where('is_self_registered_businesses', 1)
                                        ->where(function ($innerQuery) {
                                            $innerQuery->where(function ($trailQuery) {
                                                $trailQuery->whereNotNull('trail_end_date')
                                                    ->where(function ($trailEndQuery) {
                                                        $trailEndQuery->where('trail_end_date', '>', now())
                                                            ->orWhere('trail_end_date', now()->toDateString());
                                                    });
                                            })
                                                ->orWhereHas('current_subscription', function ($subQuery) {
                                                    $subQuery->where(function ($subscriptionQuery) {
                                                        $subscriptionQuery->where('start_date', '<=', now())
                                                            ->where(function ($endDateQuery) {
                                                                $endDateQuery->whereNull('end_date')
                                                                    ->orWhere('end_date', '>=', now());
                                                            });
                                                    });
                                                });
                                        });
                                });
                        });
                } else {
                    // For inactive or unsubscribed businesses
                    $subQuery->where('is_active', 0)
                        ->orWhere(function ($q) {
                            // Check for automatically subscribed businesses
                            $q->where(function ($innerQuery) {
                                $innerQuery->where('is_self_registered_businesses', 0)
                                    ->whereNull('trail_end_date')
                                    ->orWhere(function ($trailQuery) {
                                        $trailQuery->whereNotNull('trail_end_date')
                                            ->where('trail_end_date', '<', now())
                                            ->where('trail_end_date', '!=', now()->toDateString());
                                    });
                            })
                                ->orWhere(function ($q) {
                                    // Check for self-registered businesses
                                    $q->where('is_self_registered_businesses', 1)
                                        ->where(function ($innerQuery) {
                                            $innerQuery->whereNull('trail_end_date')
                                                ->orWhere(function ($trailQuery) {
                                                    $trailQuery->whereNotNull('trail_end_date')
                                                        ->where('trail_end_date', '<', now())
                                                        ->where('trail_end_date', '!=', now()->toDateString())
                                                        ->whereDoesntHave('current_subscription', function ($subQuery) {
                                                            $subQuery->where('start_date', '<=', now())
                                                                ->where(function ($endDateQuery) {
                                                                    $endDateQuery->whereNull('end_date')
                                                                        ->orWhere('end_date', '>=', now());
                                                                });
                                                        });
                                                });
                                        });
                                });
                        });
                }
            });
        });
    }























































}
