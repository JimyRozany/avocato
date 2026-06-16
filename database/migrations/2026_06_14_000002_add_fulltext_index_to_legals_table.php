<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('legals', function (Blueprint $table) {
            $table->fullText(['name', 'rule_description']);
        });
    }

    public function down(): void
    {
        Schema::table('legals', function (Blueprint $table) {
            $table->dropFullText(['name', 'rule_description']);
        });
    }
};
