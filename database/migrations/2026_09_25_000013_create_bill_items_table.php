<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bill_items', function (Blueprint $table) {
            $table->uuid('bill_item_id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('bill_id')->constrained('bills', 'bill_id')->cascadeOnDelete();
            $table->string('description');
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 12, 2)->default(0.00);
            $table->decimal('amount', 12, 2)->default(0.00);
            $table->timestamps();
            $table->index('bill_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bill_items');
    }
};
