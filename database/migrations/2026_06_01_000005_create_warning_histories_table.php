<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warning_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lawyer_id')->constrained('users')->cascadeOnDelete();
            $table->string('warning_id')->unique();
            $table->text('reason');
            $table->foreignId('sent_by')->constrained('users')->cascadeOnDelete();
            $table->date('date');
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warning_histories');
    }
};
