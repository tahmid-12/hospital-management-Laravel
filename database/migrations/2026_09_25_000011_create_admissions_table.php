<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admissions', function (Blueprint $table) {
            $table->uuid('admission_id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('patient_id')->constrained('patients', 'patient_id')->cascadeOnDelete();
            $table->foreignUuid('room_id')->constrained('rooms', 'room_id')->cascadeOnDelete();
            $table->timestamp('admitted_at')->useCurrent();
            $table->timestamp('discharged_at')->nullable();
            $table->string('status')->default('admitted');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index('patient_id');
            $table->index('room_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admissions');
    }
};
