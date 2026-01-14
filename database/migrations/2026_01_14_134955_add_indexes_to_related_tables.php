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
        // ==================== ATTENDANCES TABLE ====================
        Schema::table('attendances', function (Blueprint $table) {
            // Foreign key indexes for JOINs
            $table->index('student_id', 'idx_attendances_student');
            $table->index('class_routine_id', 'idx_attendances_routine');
            $table->index('subject_id', 'idx_attendances_subject');
            $table->index('teacher_id', 'idx_attendances_teacher');
            $table->index('session_id', 'idx_attendances_session');
            $table->index('business_id', 'idx_attendances_business');

            // Date and status for filtering
            $table->index('attendance_date', 'idx_attendances_date');
            $table->index('status', 'idx_attendances_status');

            // Composite indexes for common queries
            $table->index(['student_id', 'attendance_date'], 'idx_attendances_student_date');
            $table->index(['student_id', 'session_id'], 'idx_attendances_student_session');
            $table->index(['business_id', 'attendance_date'], 'idx_attendances_business_date');
        });

        // ==================== STUDENT_SESSIONS TABLE (PIVOT) ====================
        Schema::table('student_sessions', function (Blueprint $table) {
            // Pivot table indexes (critical for performance)
            $table->index('student_id', 'idx_student_sessions_student');
            $table->index('session_id', 'idx_student_sessions_session');

            // Composite for lookups
            $table->index(['student_id', 'session_id'], 'idx_student_sessions_composite');
        });

        // ==================== STUDENT_DOCUMENTS TABLE ====================
        Schema::table('student_documents', function (Blueprint $table) {
            $table->index('student_id', 'idx_student_documents_student');
            $table->index('business_id', 'idx_student_documents_business');
        });

        // ==================== STUDENT_LETTERS TABLE ====================
        Schema::table('student_letters', function (Blueprint $table) {
            $table->index('student_id', 'idx_student_letters_student');
            $table->index('letter_template_id', 'idx_student_letters_template');
            $table->index('business_id', 'idx_student_letters_business');
            $table->index('created_at', 'idx_student_letters_created');
        });

        // ==================== CLASS_ROUTINES TABLE ====================
        Schema::table('class_routines', function (Blueprint $table) {
            $table->index('session_id', 'idx_class_routines_session');
            $table->index('course_id', 'idx_class_routines_course');
            $table->index('subject_id', 'idx_class_routines_subject');
            $table->index('teacher_id', 'idx_class_routines_teacher');
            $table->index('business_id', 'idx_class_routines_business');
            $table->index('day_of_week', 'idx_class_routines_day');
        });

        // ==================== SESSIONS TABLE ====================
        Schema::table('sessions', function (Blueprint $table) {
            $table->index('business_id', 'idx_sessions_business');
            $table->index('start_date', 'idx_sessions_start');
            $table->index('end_date', 'idx_sessions_end');

            // Composite for current session queries
            $table->index(['start_date', 'end_date'], 'idx_sessions_dates');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop indexes in reverse order
        Schema::table('sessions', function (Blueprint $table) {
            $table->dropIndex('idx_sessions_dates');
            $table->dropIndex('idx_sessions_end');
            $table->dropIndex('idx_sessions_start');
            $table->dropIndex('idx_sessions_business');
        });

        Schema::table('class_routines', function (Blueprint $table) {
            $table->dropIndex('idx_class_routines_day');
            $table->dropIndex('idx_class_routines_business');
            $table->dropIndex('idx_class_routines_teacher');
            $table->dropIndex('idx_class_routines_subject');
            $table->dropIndex('idx_class_routines_course');
            $table->dropIndex('idx_class_routines_session');
        });

        Schema::table('student_letters', function (Blueprint $table) {
            $table->dropIndex('idx_student_letters_created');
            $table->dropIndex('idx_student_letters_business');
            $table->dropIndex('idx_student_letters_template');
            $table->dropIndex('idx_student_letters_student');
        });

        Schema::table('student_documents', function (Blueprint $table) {
            $table->dropIndex('idx_student_documents_business');
            $table->dropIndex('idx_student_documents_student');
        });

        Schema::table('student_sessions', function (Blueprint $table) {
            $table->dropIndex('idx_student_sessions_composite');
            $table->dropIndex('idx_student_sessions_session');
            $table->dropIndex('idx_student_sessions_student');
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex('idx_attendances_business_date');
            $table->dropIndex('idx_attendances_student_session');
            $table->dropIndex('idx_attendances_student_date');
            $table->dropIndex('idx_attendances_status');
            $table->dropIndex('idx_attendances_date');
            $table->dropIndex('idx_attendances_business');
            $table->dropIndex('idx_attendances_session');
            $table->dropIndex('idx_attendances_teacher');
            $table->dropIndex('idx_attendances_subject');
            $table->dropIndex('idx_attendances_routine');
            $table->dropIndex('idx_attendances_student');
        });
    }
};
