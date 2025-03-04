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
        Schema::create('parking_sticker_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('applicant_id')->nullable()->constrained()->on('users')->onDelete('cascade');
            $table->foreignId('approved_by')->nullable()->constrained()->on('users')->onDelete('cascade');
            $table->dateTime('approved_at')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained()->on('users')->onDelete('cascade');
            $table->dateTime('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->foreignId('revoked_by')->nullable()->constrained()->on('users')->onDelete('cascade');
            $table->dateTime('revoked_at')->nullable();
            $table->text('revocation_reason')->nullable();
            $table->string('department_id')->nullable()->constrained()->on('departments')->onDelete('cascade');
            $table->string('vehicle_id')->nullable()->constrained()->on('vehicles')->onDelete('cascade');
            $table->string('parking_type');
            $table->string('vehicle_color');
            $table->string('plate_number');
            $table->string('contact_number');
            $table->text('signature')->nullable();
            $table->text('orcr_attachment')->nullable();
            $table->text('assessment_attachment')->nullable(); // Assuming attachments are stored as JSON or URLs
            $table->enum('status', ['Pending', 'Active', 'Expired', 'Revoked', 'Canceled', 'Rejected'])->default('Pending');
            $table->dateTime('expiration_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parking_sticker_applications');
    }
};
