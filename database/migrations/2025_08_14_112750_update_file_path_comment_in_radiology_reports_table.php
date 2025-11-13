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
        // SQLite does not support MODIFY COLUMN comments.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // SQLite does not support MODIFY COLUMN comments.
    }
};
