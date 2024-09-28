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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->dateTime('date_requested');
            $table->foreignId('person_responsible')->constrained()->on('users')->onDelete('cascade');
            $table->foreignId('venue_id')->constrained()->on('venues')->onDelete('cascade');
            $table->string('unit_id')->nullable()->constrained()->on('units')->onDelete('cascade');
            $table->integer('participants');
            $table->string('purpose');
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->enum('status', ['Pending', 'Canceled', 'Unavailable', 'Approved', 'Ongoing', 'Ended', 'Rejected', 'Confirmed'])->default('Pending');
            $table->string('fund_source');
            $table->string('specifics')->nullable();
            $table->foreignId('noted_by')->nullable()->constrained()->on('users')->onDelete('cascade');
            $table->foreignId('approved_by')->nullable()->constrained()->on('users')->onDelete('cascade');
            $table->foreignId('approved_by_finance')->nullable()->constrained()->on('users')->onDelete('cascade');
            $table->foreignId('received_by')->nullable()->constrained()->on('users')->onDelete('cascade');
            $table->foreignId('rejected_by')->nullable()->constrained()->on('users')->onDelete('cascade');
            $table->text('rejection_reason')->nullable();
            $table->foreignId('canceled_by')->nullable()->constrained()->on('users')->onDelete('cascade');
            $table->text('cancelation_reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
