<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('payment_id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('bill_id')->constrained('bills', 'bill_id')->cascadeOnDelete();
            $table->decimal('amount', 12, 2)->default(0.00);
            $table->string('payment_method');
            $table->timestamp('paid_at')->useCurrent();
            $table->string('transaction_reference')->nullable()->unique();
            $table->timestamps();
            $table->index('bill_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
