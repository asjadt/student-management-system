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
        // ==================== COURSE_TITLES TABLE ====================
        Schema::table('course_titles', function (Blueprint $table) {
            // Foreign key indexes
            $table->index('awarding_body_id', 'idx_course_titles_awarding_body');
            $table->index('business_id', 'idx_course_titles_business');

            // Filtering and sorting
            $table->index('is_active', 'idx_course_titles_active');
            $table->index('created_at', 'idx_course_titles_created');

            // Composite for common queries
            $table->index(['business_id', 'is_active'], 'idx_course_titles_business_active');
        });

        // ==================== STUDENT_STATUSES TABLE ====================
        Schema::table('student_statuses', function (Blueprint $table) {
            $table->index('business_id', 'idx_student_statuses_business');
            $table->index('is_active', 'idx_student_statuses_active');
            $table->index('created_at', 'idx_student_statuses_created');

            // Composite for active status lookups
            $table->index(['business_id', 'is_active'], 'idx_student_statuses_business_active');
        });

        // ==================== AWARDING_BODIES TABLE ====================
        Schema::table('awarding_bodies', function (Blueprint $table) {
            $table->index('business_id', 'idx_awarding_bodies_business');
            $table->index('is_active', 'idx_awarding_bodies_active');

            // Date indexes for expiry filtering
            $table->index('accreditation_start_date', 'idx_awarding_bodies_start_date');
            $table->index('created_at', 'idx_awarding_bodies_created');

            // Composite for active body lookups
            $table->index(['business_id', 'is_active'], 'idx_awarding_bodies_business_active');
        });

        // ==================== SUBJECTS TABLE ====================
        Schema::table('subjects', function (Blueprint $table) {
            $table->index('business_id', 'idx_subjects_business');
            $table->index('is_active', 'idx_subjects_active');

            // Composite
            $table->index(['business_id', 'is_active'], 'idx_subjects_business_active');
        });

        // ==================== TEACHERS TABLE ====================
        Schema::table('teachers', function (Blueprint $table) {
            $table->index('business_id', 'idx_teachers_business');
            $table->index('is_active', 'idx_teachers_active');
            $table->index('created_at', 'idx_teachers_created');

            // Composite
            $table->index(['business_id', 'is_active'], 'idx_teachers_business_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop indexes in reverse order
        Schema::table('teachers', function (Blueprint $table) {
            $table->dropIndex('idx_teachers_business_active');
            $table->dropIndex('idx_teachers_created');
            $table->dropIndex('idx_teachers_active');
            $table->dropIndex('idx_teachers_business');
        });

        Schema::table('subjects', function (Blueprint $table) {
            $table->dropIndex('idx_subjects_business_active');
            $table->dropIndex('idx_subjects_active');
            $table->dropIndex('idx_subjects_business');
        });

        Schema::table('awarding_bodies', function (Blueprint $table) {
            $table->dropIndex('idx_awarding_bodies_business_active');
            $table->dropIndex('idx_awarding_bodies_created');
            $table->dropIndex('idx_awarding_bodies_start_date');
            $table->dropIndex('idx_awarding_bodies_active');
            $table->dropIndex('idx_awarding_bodies_business');
        });

        Schema::table('student_statuses', function (Blueprint $table) {
            $table->dropIndex('idx_student_statuses_business_active');
            $table->dropIndex('idx_student_statuses_created');
            $table->dropIndex('idx_student_statuses_active');
            $table->dropIndex('idx_student_statuses_business');
        });

        Schema::table('course_titles', function (Blueprint $table) {
            $table->dropIndex('idx_course_titles_business_active');
            $table->dropIndex('idx_course_titles_created');
            $table->dropIndex('idx_course_titles_active');
            $table->dropIndex('idx_course_titles_business');
            $table->dropIndex('idx_course_titles_awarding_body');
        });
    }
};
