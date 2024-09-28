<?php

use App\Models\Equipment;
use App\Models\JobOrder;
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
        Schema::create('job_order_equipment', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Equipment::class);
            $table->foreignIdFor(JobOrder::class);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment_job_order');
    }
};
