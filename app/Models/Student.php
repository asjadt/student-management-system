<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        "title",
        'first_name',
        'middle_name',
        'last_name',
        "student_id",
        'nationality',
        "session_id",
        "course_fee",
        "fee_paid",
        'passport_number',
        'date_of_birth',
        'course_start_date',
        'course_end_date',
        'level',
        'letter_issue_date',
        'student_status_id',
        "course_title_id",
        'attachments',
        'course_duration',
        'course_detail',
        'email',
        'contact_number',
        'sex',
        'address',
        'country',
        'city',
        'postcode',
        'lat',
        'long',
        'emergency_contact_details',
        'previous_education_history',
        'passport_issue_date',
        'passport_expiry_date',
        'place_of_issue',
        'is_active',
        'business_id',
        'is_local_student',
        'NI_number',
        'created_by',
        "image"
    ];

    protected $casts = [
        'attachments' => 'json',
        'emergency_contact_details' => 'json',
        'previous_education_history' => 'json',
    ];

    public function getPreviousEducationHistoryAttribute($value)
    {
        $history = json_decode($value, true);
        $business_name = optional($this->business)->name ?? 'unknown';

        if (isset($history['student_docs']) && is_array($history['student_docs'])) {
            foreach ($history['student_docs'] as &$student_doc_object) {
                $student_doc_object["original_file_name"] = $student_doc_object["file_name"];
                $student_doc_object["file_name"] = "/" .
                    str_replace(' ', '_', $business_name) . "/" .
                    base64_encode($this->id) . "/student_docs/" . $student_doc_object["file_name"];
            }
        }

        return $history;
    }

    public function student_documents()
    {
        return $this->hasMany(StudentDocument::class);
    }

    public function referral()
    {
        return $this->hasOne(StudentReferral::class, 'student_id');
    }

    public function course_title()
    {
        return $this->belongsTo(CourseTitle::class, 'course_title_id', 'id');
    }

    public function session()
    {
        return $this->belongsTo(Session::class, 'session_id', 'id');
    }


    // Relationships
    public function student_status()
    {
        return $this->belongsTo(StudentStatus::class, 'student_status_id');
    }

    public function business()
    {
        return $this->belongsTo(Business::class, 'business_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function student_referral()
    {
        return $this->hasOne(StudentReferral::class, 'student_id', 'id');
    }


    public function student_sessions()
    {
        return $this->hasMany(StudentSession::class, "student_id", "id");
    }

    public function sessions()
    {
        return $this->belongsToMany(Session::class, "student_sessions", "student_id", "session_id");
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class, "student_id", "id");
    }

    public function scopeFilterStudent($query, $business_setting = NULL)
    {

        $dataQuery = $query
            ->when(!empty(request()->id), function ($query) {
                return $query->where('students.id', request()->id);
            })
            ->when((request()->input("student_session_id") || request()->input("course_id") || request()->input("subject_id")), function ($query) {
                $query->whereHas("student_sessions", function ($query) {
                    $query
                        ->when(request()->filled("student_session_id"), function ($query) {
                            $query->where("student_sessions.session_id", request()->input("student_session_id"));
                        })
                        ->when(request()->input("course_id") || request()->input("subject_id"), function ($query) {
                            $query->whereHas("student_session_courses", function ($query) {
                                $query
                                    ->when(request()->filled("course_id"), function ($query) {
                                        $query->where("student_session_courses.course_title_id", request()->input("course_id"));
                                    })
                                    ->when(request()->filled("subject_id"), function ($query) {
                                        $query->whereHas("student_session_course_subjects", function ($query) {
                                            $query->where("student_course_subjects.subject_id", request()->input("subject_id"));
                                        });
                                    });
                            });
                        });
                });
            })
            // ->when(request()->filled("exclude_attendance_date"), function ($query)  {
            //     return $query->whereDoesntHave('attendances', function($query) {
            //           $query->where("attendances.attendance_date",request()->input("exclude_attendance_date"));
            //     });
            // })


            ->when(!empty(request()->nationality), function ($query) {
                return $query->where('students.nationality', request()->nationality);
            })

            ->when(!empty(request()->letter_issue_start_date), function ($query) {
                return $query->where('students.letter_issue_date', '>=', request()->letter_issue_start_date);
            })
            ->when(!empty(request()->letter_issue_end_date), function ($query) {
                return $query->where('students.letter_issue_date', '<=', request()->letter_issue_end_date . ' 23:59:59');
            })
            ->when(!empty(request()->fee_paid_min), function ($query) {
                return $query->where('students.fee_paid', '>=', request()->fee_paid_min);
            })
            ->when(!empty(request()->fee_paid_max), function ($query) {
                return $query->where('students.fee_paid', '<=', request()->fee_paid_max);
            })

            ->when(!empty(request()->course_start_date_start_date), function ($query) {
                return $query->where('students.course_start_date', '>=', request()->course_start_date_start_date);
            })
            ->when(!empty(request()->course_start_date_end_date), function ($query) {
                return $query->where('students.course_start_date', '<=', request()->course_start_date_end_date . ' 23:59:59');
            })
            ->when(!empty(request()->course_end_date_start_date), function ($query) {
                return $query->where('students.course_end_date', '>=', request()->course_end_date_start_date);
            })
            ->when(!empty(request()->course_end_date_end_date), function ($query) {
                return $query->where('students.course_end_date', '<=', request()->course_end_date_end_date . ' 23:59:59');
            })

            ->when(!empty(request()->title), function ($query) {
                return $query->where('students.title', request()->title);
            })
            ->when(!empty(request()->first_name), function ($query) {
                return $query->where('students.first_name', request()->first_name);
            })
            ->when(!empty(request()->middle_name), function ($query) {
                return $query->where('students.middle_name', request()->middle_name);
            })
            ->when(!empty(request()->last_name), function ($query) {
                return $query->where('students.last_name', request()->last_name);
            })
            ->when(!empty(request()->name), function ($query) {
                return $query->where(function ($query) {
                    $terms = explode(' ', request()->name); // Split the input into individual words
                    foreach ($terms as $term) {
                        $query
                            ->orWhere('students.title', 'like', '%' . $term . '%')
                            ->orWhere('students.first_name', 'like', '%' . $term . '%')
                            ->orWhere('students.middle_name', 'like', '%' . $term . '%')
                            ->orWhere('students.last_name', 'like', '%' . $term . '%');
                    }
                });
            })
            ->when(!empty(request()->search_key), function ($query) {
                return $query->where(function ($query) {
                    $term = request()->search_key;
                    $query->where("students.title", "like", "%" . $term . "%")
                        ->orWhere("students.first_name", "like", "%" . $term . "%")
                        ->orWhere("students.middle_name", "like", "%" . $term . "%")
                        ->orWhere("students.last_name", "like", "%" . $term . "%")
                        ->orWhere("students.nationality", "like", "%" . $term . "%")
                        ->orWhere("students.passport_number", "like", "%" . $term . "%")
                        ->orWhere("students.student_id", "like", "%" . $term . "%")
                        ->orWhere("students.date_of_birth", "like", "%" . $term . "%");
                });
            })
            //    ->when(!empty(request()->product_category_id), function ($query) use (request()) {
            //        return $query->where('product_category_id', request()->product_category_id);
            //    })
            ->when(!empty(request()->start_date), function ($query) {
                return $query->where('students.created_at', ">=", request()->start_date);
            })
            ->when(!empty(request()->end_date), function ($query) {
                return $query->where('students.created_at', "<=", (request()->end_date . ' 23:59:59'));
            })
            ->when(!empty(request()->student_status_id), function ($query) {
                return $query->where('students.student_status_id', request()->student_status_id);
            })
            ->when(
                request()->boolean("is_online_registered"),
                function ($query) use ($business_setting) {
                    // When online registration is requested, check if 'student_status_id' is NULL
                    $query->where(function ($query) use ($business_setting) {
                        $query->whereNull('students.student_status_id')
                            // Apply online status condition if business setting exists
                            ->when(!empty($business_setting) && !empty($business_setting->online_student_status_id), function ($query) use ($business_setting) {
                                $query->orWhere('students.student_status_id', $business_setting->online_student_status_id);
                            });
                    });
                },
                function ($query) use ($business_setting) {
                    // When offline registration is requested, check if 'student_status_id' is NOT NULL
                    $query
                        ->whereNotNull('students.student_status_id')
                        ->when(!empty($business_setting) && !empty($business_setting->online_student_status_id), function ($query) use ($business_setting) {
                            $query->whereNotIn('students.student_status_id', [$business_setting->online_student_status_id]);
                        })
                    ;
                }
            )



            ->when(!empty(request()->date_of_birth), function ($query) {
                return $query->where('students.date_of_birth', request()->date_of_birth);
            })
            ->when(!empty(request()->student_id), function ($query) {
                return $query->whereRaw('BINARY students.student_id = ?', [request()->student_id]);
            })

            ->when((request()->filled('is_local_student')), function ($query) {
                return $query->where('students.is_local_student', request()->boolean("is_local_student"));
            });

        return $dataQuery;
    }
}
