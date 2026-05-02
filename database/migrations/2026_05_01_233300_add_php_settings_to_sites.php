<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->string('php_memory_limit', 32)->nullable()->after('php');
            $table->string('php_upload_max_filesize', 32)->nullable()->after('php_memory_limit');
            $table->string('php_max_execution_time', 32)->nullable()->after('php_upload_max_filesize');
            $table->string('php_max_input_vars', 32)->nullable()->after('php_max_execution_time');
            $table->string('php_post_max_size', 32)->nullable()->after('php_max_input_vars');
        });
    }

    public function down(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->dropColumn([
                'php_memory_limit',
                'php_upload_max_filesize',
                'php_max_execution_time',
                'php_max_input_vars',
                'php_post_max_size',
            ]);
        });
    }
};
