<?php

return [
    "roles_permission" => [
        [
            "role" => "superadmin",
            "permissions" => [

                "letter_template_create",
                "letter_template_update",
                "letter_template_activate",
                "letter_template_view",
                "letter_template_delete",

                "module_update",
                "module_view",

                "user_create",
                "user_update",
                "user_view",
                "user_delete",

                "role_create",
                "role_update",
                "role_view",
                "role_delete",

                "business_create",
                "business_update",
                "business_view",
                "business_delete",

                "student_status_create",
                "student_status_update",
                "student_status_view",

                "installment_payment_create",
                "installment_payment_update",
                "installment_payment_activate",
                "installment_payment_view",
                "installment_payment_delete",



                "installment_plan_create",
                "installment_plan_update",
                "installment_plan_activate",
                "installment_plan_view",
                "installment_plan_delete",



                "course_title_create",
                "course_title_update",
                "course_title_view",
                "course_title_delete",



                "subject_create",
                "subject_update",
                "subject_activate",
                "subject_view",
                "subject_delete",


                "teacher_create",
                "teacher_update",
                "teacher_activate",
                "teacher_view",
                "teacher_delete",


                "class_routine_create",
                "class_routine_update",
                "class_routine_activate",
                "class_routine_view",
                "class_routine_delete",

                "attendance_create",
                "attendance_update",
                "attendance_activate",
                "attendance_view",
                "attendance_delete",

                "awarding_body_create",
                "awarding_body_update",
                "awarding_body_activate",
                "awarding_body_view",
                "awarding_body_delete",

                "business_times_update",
                "business_times_view",

            ],
        ],

        [
            "role" => "reseller",
            "permissions" => [

                "letter_template_create",
                "letter_template_update",
                "letter_template_activate",
                "letter_template_view",
                "letter_template_delete",

                "role_view",



                "user_create",
                "user_update",
                "user_view",
                "user_delete",

                "business_create",
                "business_update",
                "business_view",
                "business_delete",

                "business_times_update",
                "business_times_view",

                "student_status_create",
                "student_status_update",
                // "student_status_activate",
                "student_status_view",
                // "student_status_delete",

                "installment_payment_create",
                "installment_payment_update",
                "installment_payment_activate",
                "installment_payment_view",
                "installment_payment_delete",



                "installment_plan_create",
                "installment_plan_update",
                "installment_plan_activate",
                "installment_plan_view",
                "installment_plan_delete",



                "course_title_create",
                "course_title_update",
                "course_title_view",
                "course_title_delete",

            ],
        ],
        [
            "role" => "agency",
            "permissions" => [],
        ],
        [
            "role" => "business_admin",
            "permissions" => [

                "agency_create",
                "agency_update",
                "agency_view",
                "agency_delete",

                "student_letter_create",
                "student_letter_update",
                "student_letter_view",
                "student_letter_delete",


                "session_create",
                "session_update",
                "session_activate",
                "session_view",
                "session_delete",

                "letter_template_create",
                "letter_template_update",
                "letter_template_activate",
                "letter_template_view",
                "letter_template_delete",


                "student_create",
                "student_update",
                "student_view",
                "student_delete",

                'business_admin',

                "user_create",
                "user_update",
                "user_view",
                "user_delete",

                "business_update",
                "business_view",

                "student_status_create",
                "student_status_update",
                "student_status_activate",
                "student_status_view",
                "student_status_delete",

                "installment_payment_create",
                "installment_payment_update",
                "installment_payment_activate",
                "installment_payment_view",
                "installment_payment_delete",

                "installment_plan_create",
                "installment_plan_update",
                "installment_plan_activate",
                "installment_plan_view",
                "installment_plan_delete",

                "course_title_create",
                "course_title_update",
                "course_title_activate",
                "course_title_view",
                "course_title_delete",

                "subject_create",
                "subject_update",
                "subject_activate",
                "subject_view",
                "subject_delete",


                "teacher_create",
                "teacher_update",
                "teacher_activate",
                "teacher_view",
                "teacher_delete",

                "class_routine_create",
                "class_routine_update",
                "class_routine_activate",
                "class_routine_view",
                "class_routine_delete",

                "attendance_create",
                "attendance_update",
                "attendance_activate",
                "attendance_view",
                "attendance_delete",



                "awarding_body_create",
                "awarding_body_update",
                "awarding_body_activate",
                "awarding_body_view",
                "awarding_body_delete",

                "attendance_create",
                "attendance_approve",
                "attendance_update",
                "attendance_view",
                "attendance_delete",

                "business_times_update",
                "business_times_view",
            ],
        ],

        [
            "role" => "business_staff",
            "permissions" => [],
        ],

        [
            "role" => "business_administrator",
            "permissions" => [],
        ],
        [
            "role" => "business_teacher",
            "permissions" => [],
        ],



    ],
    "roles" => [
        "superadmin",
        'reseller',
        'agency',
        "business_admin",
        "business_staff",
        "business_administrator",
        "business_teacher",

    ],
    "permissions" => [

        "student_letter_create",
        "student_letter_update",
        "student_letter_view",
        "student_letter_delete",

        "session_create",
        "session_update",
        "session_activate",
        "session_view",
        "session_delete",

        "letter_template_create",
        "letter_template_update",
        "letter_template_activate",
        "letter_template_view",
        "letter_template_delete",

        "business_admin",

        "module_update",
        "module_view",

        "user_create",
        "user_update",
        "user_view",
        "user_delete",

        "role_create",
        "role_update",
        "role_view",
        "role_delete",

        "business_create",
        "business_update",
        "business_view",
        "business_delete",

        "agency_create",
        "agency_update",
        "agency_view",
        "agency_delete",

        "student_status_create",
        "student_status_update",
        "student_status_activate",
        "student_status_view",
        "student_status_delete",

        "installment_payment_create",
        "installment_payment_update",
        "installment_payment_activate",
        "installment_payment_view",
        "installment_payment_delete",

        "installment_plan_create",
        "installment_plan_update",
        "installment_plan_activate",
        "installment_plan_view",
        "installment_plan_delete",

        "course_title_create",
        "course_title_update",
        "course_title_activate",
        "course_title_view",
        "course_title_delete",

        "subject_create",
        "subject_update",
        "subject_activate",
        "subject_view",
        "subject_delete",

        "teacher_create",
        "teacher_update",
        "teacher_activate",
        "teacher_view",
        "teacher_delete",

        "class_routine_create",
        "class_routine_update",
        "class_routine_activate",
        "class_routine_view",
        "class_routine_delete",

        "attendance_create",
        "attendance_update",
        "attendance_activate",
        "attendance_view",
        "attendance_delete",

        "awarding_body_create",
        "awarding_body_update",
        "awarding_body_activate",
        "awarding_body_view",
        "awarding_body_delete",

        "student_create",
        "student_update",
        "student_view",
        "student_delete",

        "attendance_create",
        "attendance_update",
        "attendance_approve",
        "attendance_view",
        "attendance_delete",

        "business_times_update",
        "business_times_view",

    ],
    "permissions_titles" => [

        "module_update" => "Can enable module",
        "module_view" => "Can view module",

        "user_create" => "Can create user",
        "user_update" => "Can update user",
        "user_view" => "Can view user",
        "user_delete" => "Can delete user",

        "role_create" => "Can create role",
        "role_update" => "Can update role",
        "role_view" => "Can view role",
        "role_delete" => "Can delete role",

        "business_create" => "Can create business",
        "business_update" => "Can update business",
        "business_view" => "Can view business",
        "business_delete" => "Can delete business",

        "agency_create" => "Can create agency",
        "agency_update" => "Can create agency",
        "agency_view" => "Can create agency",
        "agency_delete" => "Can create agency",

        "student_status_create" => "Can create employment status",
        "student_status_update" => "Can update employment status",
        "student_status_activate" => "",
        "student_status_view" => "Can view employment status",
        "student_status_delete" => "Can delete employment status",

        "installment_payment_create" => "",
        "installment_payment_update" => "",
        "installment_payment_activate" => "",
        "installment_payment_view" => "",
        "installment_payment_delete" => "",

        "installment_plan_create" => "",
        "installment_plan_update" => "",
        "installment_plan_activate" => "",
        "installment_plan_view" => "",
        "installment_plan_delete" => "",

        "course_title_create" => "",
        "course_title_update" => "",
        "course_title_activate" => "",
        "course_title_view" => "",
        "course_title_delete" => "",




        "subject_create" => "",
        "subject_update" => "",
        "subject_activate" => "",
        "subject_view" => "",
        "subject_delete" => "",

        "teacher_create" => "",
        "teacher_update" => "",
        "teacher_activate" => "",
        "teacher_view" => "",
        "teacher_delete" => "",

        "class_routine_create" => "",
        "class_routine_update" => "",
        "class_routine_activate" => "",
        "class_routine_view" => "",
        "class_routine_delete" => "",

        "attendance_create" => "",
        "attendance_update" => "",
        "attendance_activate" => "",
        "attendance_view" => "",
        "attendance_delete" => "",


        "awarding_body_create" => "",
        "awarding_body_update" => "",
        "awarding_body_activate" => "",
        "awarding_body_view" => "",
        "awarding_body_delete" => "",

        "letter_template_create" => "",
        "letter_template_update" => "",
        "letter_template_activate" => "",
        "letter_template_view" => "",
        "letter_template_delete" => "",

        "student_create" => "Can create student",
        "student_update" => "Can update student",
        "student_view" => "Can view student",
        "student_delete" => "Can delete student",

        "attendance_create" => "Can create attendance",
        "attendance_update" => "Can update attendance",
        "attendance_approve" => "",
        "attendance_view" => "Can view attendance",
        "attendance_delete" => "Can delete attendance",

    ],
    "unchangeable_roles" => [
        // "superadmin",
        // "reseller"
    ],
    "unchangeable_permissions" => [
        // "business_update",
        // "business_view",
    ],

    "beautified_permissions_titles" => [

        "module_update" => "enable",
        "module_view" => "view",


        "user_create" => "create",
        "user_update" => "update",
        "user_view" => "view",
        "user_delete" => "delete",

        "role_create" => "create",
        "role_update" => "update",
        "role_view" => "view",
        "role_delete" => "delete",

        "business_create" => "create",
        "business_update" => "update",
        "business_view" => "view",
        "business_delete" => "delete",

        "agency_create" => "create",
        "agency_update" => "update",
        "agency_view" => "view",
        "agency_delete" => "delete",

        "student_status_create" => "create",
        "student_status_update" => "update",
        "student_status_activate" => "activate",
        "student_status_view" => "view",
        "student_status_delete" => "delete",


        "course_title_create" => "create",
        "course_title_update" => "update",
        "course_title_activate" => "activate",
        "course_title_view" => "view",
        "course_title_delete" => "delete",


        "student_create" => "create",
        "student_update" => "update",
        "student_view" => "view",
        "student_delete" => "delete",

        "attendance_create" => "create",
        "attendance_update" => "update",
        "attendance_approve" => "approve",
        "attendance_view" => "view",
        "attendance_delete" => "delete",

    ],

    "beautified_permissions" => [

        [
            "header" => "module",
            "permissions" => [
                "module_update",
                "module_view",

            ],
        ],

        //
        [
            "header" => "user",
            "permissions" => [
                "user_create",
                "user_update",
                "user_view",
                "user_delete",

            ],
        ],



        [
            "header" => "role",
            "permissions" => [
                "role_create",
                "role_update",
                "role_view",
                "role_delete",

            ],
        ],

        [
            "header" => "business",
            "permissions" => [
                "business_create",
                "business_update",
                "business_view",
                "business_delete",

            ],
        ],
        [
            "header" => "agency",
            "permissions" => [
                "agency_create",
                "agency_update",
                "agency_view",
                "agency_delete",

            ],
        ],


        [
            "header" => "session",
            "permissions" => [
                "session_create",
                "session_update",
                "session_activate",
                "session_view",
                "session_delete",
            ],
        ],


        [
            "header" => "student_status",
            "permissions" => [
                "student_status_create",
                "student_status_update",
                "student_status_activate",
                "student_status_view",
                "student_status_delete",





            ],
        ],



        [
            "header" => "letter_template",
            "permissions" => [

                "letter_template_create",
                "letter_template_update",
                "letter_template_activate",
                "letter_template_view",
                "letter_template_delete",


            ],
        ],




        [
            "header" => "installment_payment",
            "permissions" => [

                "installment_payment_create",
                "installment_payment_update",
                "installment_payment_activate",
                "installment_payment_view",
                "installment_payment_delete",

            ],
        ],

        [
            "header" => "installment_plan",
            "permissions" => [

                "installment_plan_create",
                "installment_plan_update",
                "installment_plan_activate",
                "installment_plan_view",
                "installment_plan_delete",

            ],
        ],



        [
            "header" => "course_title",
            "permissions" => [

                "course_title_create",
                "course_title_update",
                "course_title_activate",
                "course_title_view",
                "course_title_delete",

            ],
        ],



        [
            "header" => "class_routine",
            "permissions" => [

                "class_routine_create",
                "class_routine_update",
                "class_routine_activate",
                "class_routine_view",
                "class_routine_delete",



            ],
        ],
        [
            "header" => "attendance",
            "permissions" => [

                "attendance_create",
                "attendance_update",
                "attendance_activate",
                "attendance_view",
                "attendance_delete",

            ],
        ],



        [
            "header" => "teacher",
            "permissions" => [

                "teacher_create",
                "teacher_update",
                "teacher_activate",
                "teacher_view",
                "teacher_delete",


            ],
        ],

        [
            "header" => "subject",
            "permissions" => [

                "subject_create",
                "subject_update",
                "subject_activate",
                "subject_view",
                "subject_delete",

            ],
        ],



        [
            "header" => "awarding_body",
            "permissions" => [

                "awarding_body_create",
                "awarding_body_update",
                "awarding_body_activate",
                "awarding_body_view",
                "awarding_body_delete",

            ],
        ],


        [
            "header" => "student",
            "permissions" => [

                "student_create",
                "student_update",
                "student_view",
                "student_delete",
            ],
        ],

        [
            "header" => "attendance",
            "permissions" => [

                "attendance_create",
                "attendance_update",
                "attendance_approve",
                "attendance_view",
                "attendance_delete",




            ],
        ],

    ],




    "user_image_location" => "user_image",

    "user_files_location" => "user_files",

    "user_assets_location" => "user_assets",

    "leave_files_location" => "leave_files",

    "student_files_location" => "student_files",

    "payslip_logo_location" => "payslip_logo",






    "business_gallery_location" => "business_gallery",
    "business_background_image_location" => "business_background_image",
    "business_background_image_location_full" => "business_background_image/business_background_image.jpeg",

    "temporary_files_location" => "temporary_files",
    "folder_locations" => ["student"],

    "reminder_options"  => [
        [
            "entity_name" => "sponsorship_expiry",
            "db_table_name" => "employee_sponsorships",
            "db_field_name" => "expiry_date",

        ],

        [
            "entity_name" => "passport_expiry",
            "db_table_name" => "employee_passport_details",
            "db_field_name" => "passport_expiry_date",

        ],

        [
            "entity_name" => "visa_expiry",
            "db_table_name" => "employee_visa_details",
            "db_field_name" => "visa_expiry_date",

        ]


    ],

    // student online form dynamic fields
    "student_data_fields" => [
        [
            "section_name" => "personal_information",
            "title" => "Middle Name",
            "name" => "middle_name",
            "public_form" => [
                "show" => 1,
                "is_required" => 0
            ]
        ],
        [
            "section_name" => "contact_information",
            "title" => "Contact Number",
            "name" => "contact_number",
            "public_form" => [
                "show" => 1,
                "is_required" => 1
            ]
        ],
        [
            "section_name" => "passport_information",
            "title" => "Passport Number",
            "name" => "passport_number",
            "public_form" => [
                "show" => 1,
                "is_required" => 1
            ]
        ],
        [
            "section_name" => "passport_information",
            "title" => "Passport Issue Date",
            "name" => "passport_issue_date",
            "public_form" => [
                "show" => 1,
                "is_required" => 1
            ]
        ],
        [
            "section_name" => "passport_information",
            "title" => "Passport Expiry Date",
            "name" => "passport_expiry_date",
            "public_form" => [
                "show" => 1,
                "is_required" => 1
            ]
        ],
        [
            "section_name" => "passport_information",
            "title" => "Place of Issue",
            "name" => "place_of_issue",
            "public_form" => [
                "show" => 1,
                "is_required" => 1
            ]
        ],
        [
            "section_name" => "education_history",
            "title" => "Course Title",
            "name" => "course_title",
            "public_form" => [
                "show" => 1,
                "is_required" => 1
            ]
        ],
        [
            "section_name" => "education_history",
            "title" => "Educational Institute Name",
            "name" => "institution",
            "public_form" => [
                "show" => 1,
                "is_required" => 1
            ]
        ],
        [
            "section_name" => "education_history",
            "title" => "Passing Year",
            "name" => "education_history_year",
            "public_form" => [
                "show" => 1,
                "is_required" => 1
            ]
        ],
        [
            "section_name" => "education_history",
            "title" => "Grade",
            "name" => "education_history_grade",
            "public_form" => [
                "show" => 1,
                "is_required" => 1
            ]
        ],
        [
            "section_name" => "emergency_contact_information",
            "title" => "Emergency Contact Name",
            "name" => "name",
            "public_form" => [
                "show" => 1,
                "is_required" => 1
            ]
        ],
        [
            "section_name" => "emergency_contact_information",
            "title" => "Relationship of emergency contact person",
            "name" => "emergency_contact_relation",
            "public_form" => [
                "show" => 1,
                "is_required" => 1
            ]
        ],
        [
            "section_name" => "emergency_contact_information",
            "title" => "Emergency Contact Address",
            "name" => "address",
            "public_form" => [
                "show" => 1,
                "is_required" => 1
            ]
        ],
        [
            "section_name" => "emergency_contact_information",
            "title" => "Emergency Contact Postcode",
            "name" => "postcode",
            "public_form" => [
                "show" => 1,
                "is_required" => 1
            ]
        ],
        [
            "section_name" => "emergency_contact_information",
            "title" => "Emergency Contact Number",
            "name" => "contact",
            "public_form" => [
                "show" => 1,
                "is_required" => 1
            ]
        ]
    ],

    // student_verification_fields
    "student_verification_fields" => [
        [
            "section_name" => "personal_information",
            "title" => "First Name",
            "name" => "first_name",
            "verification_form" => [
                "show" => 1
            ]
        ],
        [
            "section_name" => "personal_information",
            "title" => "Last Name",
            "name" => "last_name",
            "verification_form" => [
                "show" => 1
            ]
        ],
        [
            "section_name" => "personal_information",
            "title" => "Date Of Birth",
            "name" => "date_of_birth",
            "verification_form" => [
                "show" => 1
            ]
        ],
        [
            "section_name" => "personal_information",
            "title" => "Student ID",
            "name" => "student_id",
            "verification_form" => [
                "show" => 1
            ]
        ],
        [
            "section_name" => "passport_information",
            "title" => "Passport Number",
            "name" => "passport_number",
            "verification_form" => [
                "show" => 1
            ]
        ]
    ],


    // system modules
    "system_modules" => [
        "agency_management",
        "class_schedule",
        "attendance_management",
        "local_student",
        "visit_log",
        "session_management",
        "letter_template",
    ]


];
