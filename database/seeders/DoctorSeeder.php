<?php

namespace Database\Seeders;

use Database\Seeders\Support\Catalog;
use Database\Seeders\Support\SeedContext;
use Database\Seeders\Support\WeightedPicker;
use Illuminate\Database\Seeder;

class DoctorSeeder extends Seeder
{
    public function run(SeedContext $ctx): void
    {
        $rng = $ctx->rng('doctors');
        $departmentWeights = array_map(fn (string $name) => Catalog::DEPARTMENTS[$name][1], $ctx->departmentNames);
        $insert = $ctx->inserter('doctors');

        foreach ($ctx->doctorPeople as $i => $person) {
            // Every department gets at least one doctor; the rest follow department size.
            $dept = $i < count($ctx->departmentIds) ? $i : $rng->weighted($departmentWeights);
            $id = $rng->uuidAt($person['ts']);

            $insert->add([
                'doctor_id' => $id,
                'user_id' => $person['user_id'],
                'department_id' => $ctx->departmentIds[$dept],
                'first_name' => $person['first'],
                'last_name' => $person['last'],
                'phone' => '01'.$rng->pick(['7', '9', '8', '6', '3']).str_pad((string) $rng->int(0, 99999999), 8, '0', STR_PAD_LEFT),
                'specialization' => $rng->pick(Catalog::DEPARTMENTS[$ctx->departmentNames[$dept]][2]),
                // Unique by construction: 10 license slots per doctor index.
                'license_number' => 'BMDC-A-'.(20000 + $i * 10 + $rng->int(0, 9)),
                'created_at' => SeedContext::datetime($person['ts']),
                'updated_at' => SeedContext::datetime($person['ts']),
            ]);

            $ctx->doctorIds[] = $id;
            $ctx->doctorDepartment[] = $dept;
            $ctx->doctorFee[] = $rng->pick([500, 700, 800, 1000, 1000, 1200, 1500, 2000]) * 100;
        }

        // A handful of "star" doctors get most appointments; others see few patients.
        $ctx->doctorPicker = WeightedPicker::zipf(count($ctx->doctorIds), config('seeding.doctor_popularity_skew'), $rng);

        $this->command->line('  doctors: '.$insert->flush());
    }
}
