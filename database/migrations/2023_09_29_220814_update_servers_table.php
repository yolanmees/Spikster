<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Encrypted payloads for these fields can exceed VARCHAR(255).
        // MySQL uses MODIFY; SQLite requires recreating the column via change().
        if (DB::getDriverName() === 'sqlite') {
            Schema::table('servers', function (Blueprint $table) {
                $table->text('password')->change();
                $table->text('database')->change();
            });
        } else {
            DB::statement('ALTER TABLE `servers` MODIFY `password` TEXT NOT NULL');
            DB::statement('ALTER TABLE `servers` MODIFY `database` TEXT NOT NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            Schema::table('servers', function (Blueprint $table) {
                $table->string('password')->change();
                $table->string('database')->change();
            });
        } else {
            DB::statement('ALTER TABLE `servers` MODIFY `password` VARCHAR(255) NOT NULL');
            DB::statement('ALTER TABLE `servers` MODIFY `database` VARCHAR(255) NOT NULL');
        }
    }
};
