<?php

namespace Database\Seeders;

use Database\Seeders\Support\Catalog;
use Database\Seeders\Support\SeedContext;
use Database\Seeders\Support\WeightedPicker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Brand-name medicines built from real generics ("Seclomox 500 mg" = Amoxicillin).
 * Popularity follows a Zipf curve, so a few brands dominate prescriptions and
 * many are rarely or never prescribed.
 */
class MedicineSeeder extends Seeder
{
    public function run(SeedContext $ctx): void
    {
        $rng = $ctx->rng('medicines');
        $generics = array_keys(Catalog::GENERICS);
        // name is unique: never generate a name that already exists.
        $taken = array_flip(DB::table('medicines')->pluck('name')->all());
        $insert = $ctx->inserter('medicines');
        $start = gmmktime(10, 0, 0, 1, 1, 2019);

        while (count($ctx->medicineIds) < $ctx->profile['medicines']) {
            $generic = $rng->pick($generics);
            [$basePrice, $forms, $strengths] = Catalog::GENERICS[$generic];
            $brand = $rng->pick(Catalog::BRAND_START).$rng->pick(Catalog::BRAND_MIDDLE).$rng->pick(Catalog::BRAND_END);
            $strength = $rng->pick($strengths);
            $form = $rng->pick($forms);
            $name = $brand.' '.$strength.($form === 'Tablet' || $form === 'Capsule' ? '' : ' '.$form);

            if (isset($taken[$name])) {
                continue;
            }
            $taken[$name] = true;

            $ts = $start + $rng->int(0, 364) * SeedContext::DAY; // all stocked before clinical data begins (2022)
            $id = $rng->uuidAt($ts);
            $packPrice = in_array($form, Catalog::PACK_FORMS, true) ? 40 : 1;
            $price = (int) round($basePrice * $packPrice * (0.7 + $rng->float() * 0.9) * 100);

            $insert->add([
                'medicine_id' => $id,
                'name' => $name,
                'description' => "Generic: {$generic}. {$form} {$strength}.",
                'dosage_form' => $form,
                'strength' => $strength,
                // ~6% out of stock, most in the hundreds, a few bulk-stocked items.
                'stock_quantity' => $rng->chance(0.06) ? 0 : (int) round(exp(5 + $rng->normal() * 1.1)),
                'created_at' => SeedContext::datetime($ts),
                'updated_at' => SeedContext::datetime($ts + $rng->int(0, 400) * SeedContext::DAY),
            ]);

            $ctx->medicineIds[] = $id;
            $ctx->medicineNames[] = $name;
            $ctx->medicineForms[] = $form;
            $ctx->medicinePrice[] = max(100, $price);
        }

        $ctx->medicinePicker = WeightedPicker::zipf(count($ctx->medicineIds), config('seeding.medicine_popularity_skew'), $rng);

        $this->command->line('  medicines: '.$insert->flush());
    }
}
