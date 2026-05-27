<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(
            "ALTER TABLE offers MODIFY COLUMN status ENUM('pending_review', 'accepted', 'rejected', 'cancelled') NOT NULL DEFAULT 'pending_review'"
        );
    }

    public function down(): void
    {
        DB::table('offers')->where('status', 'cancelled')->update(['status' => 'rejected']);

        DB::statement(
            "ALTER TABLE offers MODIFY COLUMN status ENUM('pending_review', 'accepted', 'rejected') NOT NULL DEFAULT 'pending_review'"
        );
    }
};
