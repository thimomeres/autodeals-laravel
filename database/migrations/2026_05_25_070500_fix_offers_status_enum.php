<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(
            "ALTER TABLE offers MODIFY COLUMN status ENUM('pending_review', 'approved', 'accepted', 'rejected') NOT NULL DEFAULT 'pending_review'"
        );

        DB::table('offers')->where('status', 'approved')->update(['status' => 'accepted']);

        DB::statement(
            "ALTER TABLE offers MODIFY COLUMN status ENUM('pending_review', 'accepted', 'rejected') NOT NULL DEFAULT 'pending_review'"
        );
    }

    public function down(): void
    {
        DB::statement(
            "ALTER TABLE offers MODIFY COLUMN status ENUM('pending_review', 'approved', 'accepted', 'rejected') NOT NULL DEFAULT 'pending_review'"
        );

        DB::table('offers')->where('status', 'accepted')->update(['status' => 'approved']);

        DB::statement(
            "ALTER TABLE offers MODIFY COLUMN status ENUM('pending_review', 'approved', 'rejected') NOT NULL DEFAULT 'pending_review'"
        );
    }
};
