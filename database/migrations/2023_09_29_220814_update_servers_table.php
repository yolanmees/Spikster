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
        // Encrypted payloads for these fields can exceed VARCHAR(255).
        DB::statement('ALTER TABLE `servers` MODIFY `password` TEXT NOT NULL');
        DB::statement('ALTER TABLE `servers` MODIFY `database` TEXT NOT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE `servers` MODIFY `password` VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE `servers` MODIFY `database` VARCHAR(255) NOT NULL');
    }
};
