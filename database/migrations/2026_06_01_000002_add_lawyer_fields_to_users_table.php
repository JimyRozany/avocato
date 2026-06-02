<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('bar_association_number')->nullable()->after('rate');
            $table->string('office_location')->nullable()->after('bar_association_number');
            $table->integer('years_of_experience')->nullable()->after('office_location');
            $table->string('specialty')->nullable()->after('years_of_experience');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['bar_association_number', 'office_location', 'years_of_experience', 'specialty']);
        });
    }
};
