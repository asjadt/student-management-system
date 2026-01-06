<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            // Foreign Key Indexes (for faster JOINs)
            $table->index('course_title_id', 'idx_students_course_title');
            $table->index('session_id', 'idx_students_session');
            $table->index('student_status_id', 'idx_students_status');
            $table->index('business_id', 'idx_students_business');
            $table->index('created_by', 'idx_students_creator');

            // Search Indexes
            $table->index('student_id', 'idx_students_student_id');
            $table->index('passport_number', 'idx_students_passport');
            $table->index('email', 'idx_students_email');

            // Date Range Indexes (for filtering)
            $table->index('course_start_date', 'idx_students_course_start');
            $table->index('course_end_date', 'idx_students_course_end');
            $table->index('letter_issue_date', 'idx_students_letter_date');
            $table->index('created_at', 'idx_students_created');

            // Composite Indexes (for common query patterns)
            $table->index(['business_id', 'student_status_id'], 'idx_students_business_status');
            $table->index(['business_id', 'created_at'], 'idx_students_business_created');
            $table->index(['course_start_date', 'course_end_date'], 'idx_students_course_dates');
            $table->index(['student_status_id', 'course_start_date'], 'idx_students_status_date');

            // Boolean flag index
            $table->index('is_active', 'idx_students_active');
            $table->index('is_local_student', 'idx_students_local');

            // Composite for local/international filtering
            $table->index(['business_id', 'is_local_student'], 'idx_students_business_local');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            // Drop indexes in reverse order
            $table->dropIndex('idx_students_business_local');
            $table->dropIndex('idx_students_local');
            $table->dropIndex('idx_students_active');
            $table->dropIndex('idx_students_status_date');
            $table->dropIndex('idx_students_course_dates');
            $table->dropIndex('idx_students_business_created');
            $table->dropIndex('idx_students_business_status');
            $table->dropIndex('idx_students_created');
            $table->dropIndex('idx_students_letter_date');
            $table->dropIndex('idx_students_course_end');
            $table->dropIndex('idx_students_course_start');
            $table->dropIndex('idx_students_email');
            $table->dropIndex('idx_students_passport');
            $table->dropIndex('idx_students_student_id');
            $table->dropIndex('idx_students_creator');
            $table->dropIndex('idx_students_business');
            $table->dropIndex('idx_students_status');
            $table->dropIndex('idx_students_session');
            $table->dropIndex('idx_students_course_title');
        });
    }
};
