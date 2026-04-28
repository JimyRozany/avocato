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

/**
 * 'case_number
'title
'description
'client_id
'lawyer_id
'case_type_id
'court_id
'opponent_id
'status
'filed_date
'first_hearing_date
'case_value
 */
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cases');
    }
};
