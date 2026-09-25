<?php

namespace Database\Seeders;

use Database\Seeders\Support\Catalog;
use Database\Seeders\Support\SeedContext;
use Illuminate\Database\Seeder;

/**
 * 1-8 distinct medicines per prescription (mostly 2-3), drawn from the Zipf
 * popularity curve. Refills copy the original prescription's medicines.
 * The dispensed quantity is remembered for pharmacy bills.
 */
class PrescriptionItemSeeder extends Seeder
{
    private const ITEM_COUNT = [1 => 15, 2 => 30, 3 => 27, 4 => 16, 5 => 8, 6 => 3, 8 => 1];

    public function run(SeedContext $ctx): void
    {
        $rng = $ctx->rng('prescription_items');
        $insert = $ctx->inserter('prescription_items');

        foreach ($ctx->prescriptionIds as $index => $prescriptionId) {
            $refillOf = $ctx->prescriptionRefillOf[$index];
            $medicines = [];

            if ($refillOf >= 0) {
                foreach (explode(';', $ctx->prescriptionItems[$refillOf]) as $item) {
                    $medicines[(int) $item] = true;
                }
            } else {
                for ($n = $rng->weighted(self::ITEM_COUNT), $tries = 0; count($medicines) < $n && $tries < 20; $tries++) {
                    $medicines[$ctx->medicinePicker->pick($rng)] = true;
                }
            }

            $ts = $ctx->prescriptionTs[$index];
            $dispensed = [];

            foreach (array_keys($medicines) as $medicine) {
                $form = $ctx->medicineForms[$medicine];
                $frequency = $rng->weighted(Catalog::FREQUENCY_WEIGHTS);
                $duration = $refillOf >= 0 ? '30 days' : $rng->weighted(Catalog::DURATION_WEIGHTS);
                $quantity = in_array($form, Catalog::PACK_FORMS, true)
                    ? $rng->int(1, 2)
                    : Catalog::FREQUENCIES[$frequency] * Catalog::DURATIONS[$duration];

                $insert->add([
                    'prescription_item_id' => $rng->uuidAt($ts),
                    'prescription_id' => $prescriptionId,
                    'medicine_id' => $ctx->medicineIds[$medicine],
                    'dosage' => Catalog::FORM_DOSAGE[$form],
                    'frequency' => $frequency,
                    'duration' => $duration,
                    'instructions' => $rng->pick(Catalog::INSTRUCTIONS),
                    'created_at' => SeedContext::datetime($ts),
                    'updated_at' => SeedContext::datetime($ts),
                ]);

                $dispensed[] = $medicine.':'.$quantity;
            }

            // Compact "medicine:qty;medicine:qty" string: far less memory than nested arrays.
            $ctx->prescriptionItems[$index] = implode(';', $dispensed);
        }

        $this->command->line('  prescription_items: '.$insert->flush());
    }
}
