<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add FULLTEXT index for fast searching on name fields and identifiers
        DB::statement('ALTER TABLE students ADD FULLTEXT idx_students_fulltext_search (first_name, last_name, student_id, passport_number)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop FULLTEXT index
        DB::statement('ALTER TABLE students DROP INDEX idx_students_fulltext_search');
    }
};
