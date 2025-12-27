<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // SQLite doesn't support ALTER COLUMN easily, so we use raw SQL
        // First, check if column exists and what type it is
        $connection = Schema::getConnection();
        
        // Get table info
        $tableInfo = $connection->select("PRAGMA table_info(answers)");
        $hasSubmissionId = false;
        
        foreach ($tableInfo as $column) {
            if ($column->name === 'submission_id') {
                $hasSubmissionId = true;
                // If it's already TEXT/VARCHAR, we're good
                if (strtolower($column->type) === 'text' || strtolower($column->type) === 'varchar') {
                    return; // Already correct type
                }
                break;
            }
        }
        
        if (!$hasSubmissionId) {
            // Column doesn't exist, add it
            Schema::table('answers', function (Blueprint $table) {
                $table->string('submission_id')->nullable()->after('id');
                $table->index('submission_id');
            });
            return;
        }
        
        // For SQLite, we need to recreate the table to change column type
        // This is a complex operation, so we'll use a workaround:
        // Since SQLite is type-affinity based, we can just ensure the column accepts strings
        // by recreating the table structure
        
        // Step 1: Create new table with correct structure
        $connection->statement('
            CREATE TABLE answers_new (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                submission_id TEXT,
                question_id INTEGER NOT NULL,
                answer VARCHAR NOT NULL,
                created_at DATETIME,
                updated_at DATETIME,
                FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE
            )
        ');
        
        // Step 2: Copy data (convert submission_id to string if needed)
        $connection->statement('
            INSERT INTO answers_new (id, submission_id, question_id, answer, created_at, updated_at)
            SELECT id, 
                   CASE 
                       WHEN submission_id IS NULL THEN NULL 
                       ELSE CAST(submission_id AS TEXT) 
                   END,
                   question_id, answer, created_at, updated_at
            FROM answers
        ');
        
        // Step 3: Drop old table
        Schema::drop('answers');
        
        // Step 4: Rename new table
        $connection->statement('ALTER TABLE answers_new RENAME TO answers');
        
        // Step 5: Recreate index
        Schema::table('answers', function (Blueprint $table) {
            $table->index('submission_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('answers', function (Blueprint $table) {
            if (Schema::hasColumn('answers', 'submission_id')) {
                $table->dropIndex(['submission_id']);
                $table->dropColumn('submission_id');
            }
        });
    }
};
