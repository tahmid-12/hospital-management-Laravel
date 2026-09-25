<?php

namespace Database\Seeders;

use Database\Seeders\Support\SeedContext;
use Illuminate\Database\Seeder;

/**
 * Bills come from real events, so busy patients naturally get many bills:
 *   consultation - most completed visits
 *   lab          - some visits order tests
 *   admission    - every hospital stay (interim 'pending' bill while still admitted)
 *   pharmacy     - most prescriptions are filled at the hospital pharmacy
 *
 * total_amount is inserted as 0 and set by BillItemSeeder from the sum of the items.
 * Bills are inserted in date order, as a live system would write them.
 */
class BillSeeder extends Seeder
{
    public const CONSULTATION = 0;

    public const LAB = 1;

    public const ADMISSION = 2;

    public const PHARMACY = 3;

    public function run(SeedContext $ctx): void
    {
        $rng = $ctx->rng('bills');
        $kinds = [];
        $refs = [];
        $times = [];

        $add = function (int $kind, int $ref, int $ts) use (&$kinds, &$refs, &$times, $ctx) {
            $kinds[] = $kind;
            $refs[] = $ref;
            $times[] = min($ts, $ctx->referenceTs);
        };

        foreach ($ctx->visitTs as $visit => $ts) {
            if ($rng->chance(0.97)) {
                $add(self::CONSULTATION, $visit, $ts + $rng->int(1200, 7200));
            }
            if ($rng->chance(0.15)) {
                $add(self::LAB, $visit, $ts + $rng->int(0, 3) * SeedContext::DAY + $rng->int(1800, 14400));
            }
        }
        foreach ($ctx->admissionStart as $admission => $start) {
            $add(self::ADMISSION, $admission, $ctx->admissionEnd[$admission] ?? $start + 3600);
        }
        foreach ($ctx->prescriptionTs as $prescription => $ts) {
            if ($rng->chance(0.8)) {
                $add(self::PHARMACY, $prescription, $ts + $rng->int(600, 7200));
            }
        }

        // Sort the three parallel arrays by bill time (stable, so ties stay deterministic).
        array_multisort($times, SORT_ASC, SORT_NUMERIC, $kinds, $refs);

        $statuses = config('seeding.bill_status');
        $oneYearAgo = $ctx->referenceTs - 365 * SeedContext::DAY;
        $insert = $ctx->inserter('bills');

        foreach ($times as $i => $ts) {
            $kind = $kinds[$i];
            $ref = $refs[$i];
            $patient = match ($kind) {
                self::CONSULTATION, self::LAB => $ctx->visitPatient[$ref],
                self::ADMISSION => $ctx->admissionPatient[$ref],
                self::PHARMACY => $ctx->prescriptionPatient[$ref],
            };
            $status = $kind === self::ADMISSION && $ctx->admissionEnd[$ref] === null
                ? 'pending'
                : $rng->weighted($ts < $oneYearAgo ? $statuses['older_than_a_year'] : $statuses['recent']);

            $id = $rng->uuidAt($ts);
            $insert->add([
                'bill_id' => $id,
                'patient_id' => $ctx->patientIds[$patient],
                'bill_date' => SeedContext::date($ts),
                'total_amount' => '0.00',
                'status' => $status,
                'created_at' => SeedContext::datetime($ts),
                'updated_at' => SeedContext::datetime($ts),
            ]);

            $ctx->billIds[] = $id;
            $ctx->billKind[] = $kind;
            $ctx->billRef[] = $ref;
            $ctx->billTs[] = $ts;
            $ctx->billStatus[] = $status;
        }

        $this->command->line('  bills: '.$insert->flush());
    }
}
