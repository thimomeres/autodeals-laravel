<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Role "customer" untuk akun aplikasi mobile (register via API).
     * Kolom role sudah string(20); migrasi ini dokumentasi + normalisasi default.
     */
    public function up(): void
    {
        DB::table('users')
            ->whereNull('role')
            ->update(['role' => 'staff']);
    }

    public function down(): void
    {
        // Tidak menghapus akun customer — rollback manual jika diperlukan.
    }
};
