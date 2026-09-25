<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medical_records', function (Blueprint $table) {
            $table->uuid('medical_record_id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('patient_id')->constrained('patients', 'patient_id')->cascadeOnDelete();
            $table->foreignUuid('doctor_id')->constrained('doctors', 'doctor_id')->cascadeOnDelete();
            $table->foreignUuid('appointment_id')->nullable()->constrained('appointments', 'appointment_id')->nullOnDelete();
            $table->text('diagnosis')->nullable();
            $table->text('symptoms')->nullable();
            $table->text('treatment_notes')->nullable();
            $table->timestamps();
            $table->index('patient_id');
            $table->index('doctor_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medical_records');
    }
};
