<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prescription_items', function (Blueprint $table) {
            $table->uuid('prescription_item_id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('prescription_id')->constrained('prescriptions', 'prescription_id')->cascadeOnDelete();
            $table->foreignUuid('medicine_id')->constrained('medicines', 'medicine_id')->cascadeOnDelete();
            $table->string('dosage')->nullable();
            $table->string('frequency')->nullable();
            $table->string('duration')->nullable();
            $table->text('instructions')->nullable();
            $table->timestamps();
            $table->index('prescription_id');
            $table->index('medicine_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prescription_items');
    }
};
