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
        Schema::create('job_orders', function (Blueprint $table) {
            $table->id();
            $table->string('job_order_title');
            $table->string('unit_name', 255);
            $table->dateTime('date_requested');
            $table->dateTime('date_needed');
            $table->longText('particulars');
            $table->text('materials')
                ->nullable();
            $table->string('assigned_role')->nullable();
            $table->foreignId('requested_by')->nullable()->constrained()->on('users')->onDelete('cascade');
            $table->foreignId('canceled_by')->nullable()->constrained()->on('users')->onDelete('cascade');
            $table->dateTime('canceled_at')->nullable();
            $table->text('cancelation_reason')->nullable();
            $table->foreignId('recommended_by')->nullable()->constrained()->on('users')->onDelete('cascade');
            $table->dateTime('recommended_at')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained()->on('users')->onDelete('cascade');
            $table->dateTime('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained()->on('users')->onDelete('cascade');
            $table->dateTime('approved_at')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained()->on('users')->onDelete('cascade');
            $table->foreignId('accomplished_by')->nullable()->constrained()->on('users')->onDelete('cascade');
            $table->dateTime('accomplished_at')->nullable();
            $table->foreignId('checked_by')->nullable()->constrained()->on('users')->onDelete('cascade');
            $table->dateTime('checked_at')->nullable();
            $table->foreignId('confirmed_by')->nullable()->constrained()->on('users')->onDelete('cascade');
            $table->dateTime('confirmed_at')->nullable();
            $table->enum('status', ['Pending', 'Canceled', 'Rejected', 'Assigned', 'Completed',])->default('Pending');
            $table->dateTime('date_begun')
                ->nullable();
            $table->dateTime('date_completed')
                ->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_orders');
    }
};
