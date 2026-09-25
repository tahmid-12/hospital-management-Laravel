<?php

namespace Database\Seeders;

use Database\Seeders\Support\SeedContext;
use Illuminate\Database\Seeder;

/**
 * Payments that agree with each bill's status:
 *   paid           - installments that add up exactly to total_amount
 *   partially_paid - installments covering 20-80% of total_amount
 *   pending/cancelled - no payments
 * Payments never happen before the bill or after the reference date.
 * Large admission bills are paid in more installments.
 */
class PaymentSeeder extends Seeder
{
    private const INSTALLMENTS = [1 => 60, 2 => 25, 3 => 10, 4 => 5];

    private const ADMISSION_INSTALLMENTS = [1 => 30, 2 => 35, 3 => 20, 4 => 10, 5 => 5];

    private const REFERENCE_PREFIX = ['bkash' => 'BK', 'nagad' => 'NG', 'card' => 'CD', 'bank_transfer' => 'BT', 'insurance' => 'IN'];

    public function run(SeedContext $ctx): void
    {
        $rng = $ctx->rng('payments');
        $methods = config('seeding.payment_methods');
        $insert = $ctx->inserter('payments');
        $reference = 0;

        foreach ($ctx->billIds as $bill => $billId) {
            $status = $ctx->billStatus[$bill];
            $total = $ctx->billTotal[$bill];

            $target = match ($status) {
                'paid' => $total,
                'partially_paid' => (int) round($total * (0.2 + $rng->float() * 0.6)),
                default => 0,
            };
            if ($target <= 0) {
                continue;
            }

            $count = $rng->weighted($ctx->billKind[$bill] === BillSeeder::ADMISSION ? self::ADMISSION_INSTALLMENTS : self::INSTALLMENTS);
            if ($status === 'partially_paid') {
                $count = min($count, 2);
            }

            // First payment: usually at the counter the same day, sometimes days later.
            $ts = $ctx->billTs[$bill] + ($rng->chance(0.6) ? $rng->int(60, 7200) : $rng->int(1, 10) * SeedContext::DAY);
            $remaining = $target;

            for ($k = 1; $k <= $count && $remaining > 0; $k++) {
                $ts = min($ts, $ctx->referenceTs);
                $isLast = $k === $count || $ts + 7 * SeedContext::DAY > $ctx->referenceTs;
                $amount = $isLast ? $remaining : max(1, (int) round($remaining * (0.3 + $rng->float() * 0.4)));
                $remaining -= $amount;
                $method = $rng->weighted($methods);

                $insert->add([
                    'payment_id' => $rng->uuidAt($ts),
                    'bill_id' => $billId,
                    'amount' => SeedContext::money($amount),
                    'payment_method' => $method,
                    'paid_at' => SeedContext::datetime($ts),
                    // Unique by construction: method prefix + date + running counter. Cash has no reference.
                    'transaction_reference' => $method === 'cash' ? null
                        : self::REFERENCE_PREFIX[$method].gmdate('ymd', $ts).str_pad((string) ++$reference, 8, '0', STR_PAD_LEFT),
                    'created_at' => SeedContext::datetime($ts),
                    'updated_at' => SeedContext::datetime($ts),
                ]);

                if ($isLast) {
                    break;
                }
                $ts += $rng->int(7, 45) * SeedContext::DAY;
            }
        }

        $this->command->line('  payments: '.$insert->flush());
    }
}
