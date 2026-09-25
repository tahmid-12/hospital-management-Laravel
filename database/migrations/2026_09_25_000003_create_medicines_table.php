<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medicines', function (Blueprint $table) {
            $table->uuid('medicine_id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->string('dosage_form')->nullable();
            $table->string('strength')->nullable();
            $table->integer('stock_quantity')->default(0);
            $table->timestamps();
            $table->index('stock_quantity');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medicines');
    }
};
