<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("
            ALTER TABLE radiology_reports
            MODIFY file_path VARCHAR(255) NULL COMMENT 'Path to the uploaded report file, e.g., PDF'
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("
            ALTER TABLE radiology_reports
            MODIFY file_path VARCHAR(255) NULL COMMENT 'Path to the uploaded report file'
        ");
    }
};
