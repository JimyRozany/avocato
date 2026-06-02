<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('cases')->where('status', 'open')->update(['status' => 'active']);
        DB::table('cases')->where('status', 'close')->update(['status' => 'closed']);

        Schema::table('cases', function (Blueprint $table) {
            $table->string('status')->default('pending')->change();
        });
    }

    public function down(): void
    {
        DB::table('cases')->where('status', 'active')->update(['status' => 'open']);
        DB::table('cases')->where('status', 'closed')->update(['status' => 'close']);

        Schema::table('cases', function (Blueprint $table) {
            $table->string('status')->default('open')->change();
        });
    }
};
