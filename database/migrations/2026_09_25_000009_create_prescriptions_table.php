<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->uuid('prescription_id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('patient_id')->constrained('patients', 'patient_id')->cascadeOnDelete();
            $table->foreignUuid('doctor_id')->constrained('doctors', 'doctor_id')->cascadeOnDelete();
            $table->foreignUuid('appointment_id')->nullable()->constrained('appointments', 'appointment_id')->nullOnDelete();
            $table->timestamp('prescribed_at')->useCurrent();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index('patient_id');
            $table->index('doctor_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prescriptions');
    }
};
