<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('warning_histories', function (Blueprint $table) {
            $table->string('warning_for', 20)->default('lawyer')->after('lawyer_id');
        });
    }

    public function down(): void
    {
        Schema::table('warning_histories', function (Blueprint $table) {
            $table->dropColumn('warning_for');
        });
    }
};
