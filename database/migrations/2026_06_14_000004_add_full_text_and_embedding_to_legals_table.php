<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('legals', function (Blueprint $table) {
            $table->longText('full_text')->nullable()->after('rule_description');
            $table->json('embedding')->nullable()->after('full_text');
        });
    }

    public function down(): void
    {
        Schema::table('legals', function (Blueprint $table) {
            $table->dropColumn(['full_text', 'embedding']);
        });
    }
};
