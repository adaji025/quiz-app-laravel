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
        // For SQLite, we need to handle column type changes differently
        if (Schema::hasColumn('answers', 'submission_id')) {
            // Column exists, just add index if needed
            Schema::table('answers', function (Blueprint $table) {
                $table->index('submission_id');
            });
        } else {
            // Column doesn't exist, add it
            Schema::table('answers', function (Blueprint $table) {
                $table->string('submission_id')->nullable()->after('id');
                $table->index('submission_id');
            });
        }
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
