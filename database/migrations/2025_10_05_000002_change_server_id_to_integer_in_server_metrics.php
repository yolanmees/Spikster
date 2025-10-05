<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Change server_id from string to unsignedBigInteger to match the servers.id column.
     */
    public function up(): void
    {
        // Drop existing indexes first
        Schema::table('server_metrics', function (Blueprint $table) {
            $table->dropIndex(['server_id', 'measured_at']);
            $table->dropIndex(['server_id', 'created_at']);
            $table->dropIndex(['server_id']);
        });

        // Truncate table to avoid data type conflicts
        // This is safe because we're in development and old data format was wrong anyway
        DB::table('server_metrics')->truncate();

        // Change column type
        Schema::table('server_metrics', function (Blueprint $table) {
            $table->unsignedBigInteger('server_id')->change();
        });

        // Recreate indexes
        Schema::table('server_metrics', function (Blueprint $table) {
            $table->index('server_id');
            $table->index(['server_id', 'measured_at']);
            $table->index(['server_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop indexes
        Schema::table('server_metrics', function (Blueprint $table) {
            $table->dropIndex(['server_id', 'measured_at']);
            $table->dropIndex(['server_id', 'created_at']);
            $table->dropIndex(['server_id']);
        });

        // Truncate table
        DB::table('server_metrics')->truncate();

        // Change back to string
        Schema::table('server_metrics', function (Blueprint $table) {
            $table->string('server_id')->change();
        });

        // Recreate indexes
        Schema::table('server_metrics', function (Blueprint $table) {
            $table->index('server_id');
            $table->index(['server_id', 'measured_at']);
            $table->index(['server_id', 'created_at']);
        });
    }
};
