<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
       Schema::create('cases', function (Blueprint $table) {
    $table->id();
    $table->string('case_number')->unique();
    $table->string('title');
    $table->text('description')->nullable();
    $table->string('type')->nullable(); // civil, criminal
    $table->string('status')->default('open');
    $table->string('court_name')->nullable();
    $table->date('start_date')->nullable();

    $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();

    $table->timestamps();
    $table->softDeletes();
});


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cases');
    }
};
