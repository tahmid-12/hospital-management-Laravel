<?php

namespace Database\Seeders;

use Database\Seeders\Support\Catalog;
use Database\Seeders\Support\Rng;
use Database\Seeders\Support\SeedContext;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Line items per bill kind (consultation fee, lab tests, room nights, medicines),
 * then one set-based UPDATE writes every bill's total_amount = SUM(items.amount).
 */
class BillItemSeeder extends Seeder
{
    public function run(SeedContext $ctx): void
    {
        $rng = $ctx->rng('bill_items');
        $labTests = array_keys(Catalog::LAB_TESTS);
        $labWeights = array_map(fn (array $t) => $t[1], array_values(Catalog::LAB_TESTS));
        $procedures = array_keys(Catalog::PROCEDURES);
        $extras = array_keys(Catalog::ADMISSION_EXTRAS);
        $insert = $ctx->inserter('bill_items');

        foreach ($ctx->billIds as $bill => $billId) {
            $ref = $ctx->billRef[$bill];
            $ts = SeedContext::datetime($ctx->billTs[$bill]);
            $total = 0;

            // [description, quantity, unit price in cents]
            $lines = match ($ctx->billKind[$bill]) {
                BillSeeder::CONSULTATION => $this->consultation($ctx, $rng, $ref, $procedures),
                BillSeeder::LAB => $this->lab($rng, $labTests, $labWeights),
                BillSeeder::ADMISSION => $this->admission($ctx, $rng, $ref, $extras),
                BillSeeder::PHARMACY => $this->pharmacy($ctx, $ref),
            };

            foreach ($lines as [$description, $quantity, $unitPrice]) {
                $amount = $quantity * $unitPrice;
                $total += $amount;

                $insert->add([
                    'bill_item_id' => $rng->uuidAt($ctx->billTs[$bill]),
                    'bill_id' => $billId,
                    'description' => $description,
                    'quantity' => $quantity,
                    'unit_price' => SeedContext::money($unitPrice),
                    'amount' => SeedContext::money($amount),
                    'created_at' => $ts,
                    'updated_at' => $ts,
                ]);
            }

            $ctx->billTotal[$bill] = $total;
        }

        $inserted = $insert->flush();

        // Set-based: PostgreSQL aggregates and updates every bill in one statement.
        // Only bills still at 0.00 (i.e. the ones just inserted) are touched.
        $updated = DB::update(<<<'SQL'
            UPDATE bills AS b
            SET total_amount = s.total
            FROM (SELECT bill_id, SUM(amount) AS total FROM bill_items GROUP BY bill_id) AS s
            WHERE b.bill_id = s.bill_id AND b.total_amount = 0
            SQL);

        $this->command->line("  bill_items: {$inserted} (bill totals updated: {$updated})");
    }

    private function consultation(SeedContext $ctx, Rng $rng, int $visit, array $procedures): array
    {
        $doctor = $ctx->visitDoctor[$visit];
        $lines = [['Consultation fee - '.$ctx->departmentNames[$ctx->doctorDepartment[$doctor]], 1, $ctx->doctorFee[$doctor]]];

        for ($n = $rng->weighted([0 => 55, 1 => 30, 2 => 15]); $n > 0; $n--) {
            $procedure = $rng->pick($procedures);
            $lines[] = [$procedure, 1, Catalog::PROCEDURES[$procedure] * 100];
        }

        return $lines;
    }

    private function lab(Rng $rng, array $tests, array $weights): array
    {
        $chosen = [];
        for ($n = $rng->weighted([1 => 35, 2 => 30, 3 => 20, 4 => 10, 5 => 5]); count($chosen) < $n;) {
            $chosen[$rng->weighted($weights)] = true;
        }

        return array_map(fn (int $t) => [$tests[$t], 1, Catalog::LAB_TESTS[$tests[$t]][0] * 100], array_keys($chosen));
    }

    private function admission(SeedContext $ctx, Rng $rng, int $admission, array $extras): array
    {
        $room = $ctx->admissionRoom[$admission];
        $type = $ctx->roomTypes[$room];
        $end = $ctx->admissionEnd[$admission] ?? $ctx->referenceTs;
        $nights = max(1, (int) ceil(($end - $ctx->admissionStart[$admission]) / SeedContext::DAY));

        $lines = [
            ['Admission fee', 1, 100000],
            ["Room charge - {$type}", $nights, $ctx->roomRate[$room]],
            ['Nursing care', $nights, Catalog::ROOM_TYPES[$type][3] * 100],
            ['Consultant round', $nights, $rng->pick([800, 1000, 1500]) * 100],
        ];

        $picked = [];
        for ($n = $rng->int(0, 4); count($picked) < $n;) {
            $picked[$rng->pick($extras)] = true;
        }
        foreach (array_keys($picked) as $extra) {
            [$min, $max] = Catalog::ADMISSION_EXTRAS[$extra];
            $lines[] = [$extra, 1, $rng->int($min, $max) * 100];
        }

        return $lines;
    }

    private function pharmacy(SeedContext $ctx, int $prescription): array
    {
        $lines = [];
        foreach (explode(';', $ctx->prescriptionItems[$prescription]) as $item) {
            [$medicine, $quantity] = array_map('intval', explode(':', $item));
            $lines[] = [$ctx->medicineNames[$medicine], $quantity, $ctx->medicinePrice[$medicine]];
        }

        return $lines;
    }
}
