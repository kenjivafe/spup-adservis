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
        Schema::create('equipment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained();
            $table->foreignId('equipment_category_id')->constrained();
            $table->foreignId('equipment_brand_id')->constrained();
            $table->foreignId('equipment_type_id')->constrained();
            $table->string('code')->unique();
            $table->enum('status', ['Active', 'Inactive', 'Disposed'])->default('Active');
            $table->enum('disposal_reason', ['Unrepairable', 'Obsolete', 'Stolen'])->nullable();
            $table->date('date_acquired')->nullable();
            $table->date('date_disposed')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment');
    }
};
