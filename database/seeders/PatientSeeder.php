<?php

namespace Database\Seeders;

use Database\Seeders\Support\Catalog;
use Database\Seeders\Support\Rng;
use Database\Seeders\Support\SeedContext;
use Illuminate\Database\Seeder;

/**
 * Patients with an account reuse the people planned by UserSeeder; the rest
 * are walk-ins with user_id = NULL (useful for IS NULL / outer join practice).
 */
class PatientSeeder extends Seeder
{
    public function run(SeedContext $ctx): void
    {
        $rng = $ctx->rng('patients');
        $people = $ctx->patientPeople;

        for ($i = count($people); $i < $ctx->profile['patients']; $i++) {
            $gender = $rng->chance(0.5) ? 'male' : 'female';
            $people[] = [
                'user_id' => null,
                'first' => $rng->pick($gender === 'female' ? Catalog::FEMALE_FIRST_NAMES : Catalog::MALE_FIRST_NAMES),
                'last' => $rng->pick(Catalog::LAST_NAMES),
                'gender' => $gender,
                'reg_day' => $ctx->sampleDay($rng, 0, $ctx->referenceDay, registration: true),
            ];
        }

        usort($people, fn ($a, $b) => $a['reg_day'] <=> $b['reg_day']);

        $activityWeights = array_map(fn (array $a) => $a['weight'], config('seeding.patient_activity'));
        $phones = [];
        $insert = $ctx->inserter('patients');

        foreach ($people as $person) {
            $ts = $person['reg_day'] * SeedContext::DAY + $rng->int(28800, 72000);
            $id = $rng->uuidAt($ts);
            $phone = $this->phone($rng, $phones);

            $insert->add([
                'patient_id' => $id,
                'user_id' => $person['user_id'],
                'first_name' => $person['first'],
                'last_name' => $person['last'],
                'date_of_birth' => SeedContext::date($this->birthTs($rng, $ts)),
                'gender' => $person['gender'],
                'phone' => $phone,
                'address' => 'House '.$rng->int(1, 120).', Road '.$rng->int(1, 40).', '.$rng->weighted(Catalog::AREAS),
                'blood_group' => $rng->chance(0.85) ? $rng->weighted(Catalog::BLOOD_GROUPS) : null,
                'emergency_contact' => $rng->chance(0.8)
                    ? $rng->pick(Catalog::MALE_FIRST_NAMES).' '.$person['last'].' ('.$rng->pick(Catalog::RELATIONS).') '.$this->phone($rng, $phones, false)
                    : null,
                'created_at' => SeedContext::datetime($ts),
                'updated_at' => SeedContext::datetime($ts),
            ]);

            $ctx->patientIds[] = $id;
            $ctx->patientRegDay[] = $person['reg_day'];
            $ctx->patientActivity[] = $rng->weighted($activityWeights);
            $ctx->patientPrimaryDoctor[] = $ctx->doctorPicker->pick($rng);
        }

        $this->command->line('  patients: '.$insert->flush());
    }

    /** Bangladeshi mobile number. ~4% share a family member's number, ~3% have none. */
    private function phone(Rng $rng, array &$issued, bool $allowMissing = true): ?string
    {
        if ($allowMissing && $rng->chance(0.03)) {
            return null;
        }
        if ($issued !== [] && $rng->chance(0.04)) {
            return $rng->pick($issued);
        }

        $phone = $rng->weighted(Catalog::PHONE_PREFIXES).str_pad((string) $rng->int(0, 99999999), 8, '0', STR_PAD_LEFT);
        $issued[] = $phone;

        return $phone;
    }

    private function birthTs(Rng $rng, int $registeredTs): int
    {
        [$min, $max] = array_map('intval', explode('-', $rng->weighted(Catalog::AGE_BANDS)));
        $ageDays = $rng->int($min * 365, $max * 365 + 364);

        return $registeredTs - $ageDays * SeedContext::DAY;
    }
}
