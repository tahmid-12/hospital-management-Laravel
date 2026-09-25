<?php

namespace Database\Seeders;

use Database\Seeders\Support\Catalog;
use Database\Seeders\Support\SeedContext;
use Illuminate\Database\Seeder;

/**
 * Creates login accounts for admins, staff, doctors and most patients.
 * The people behind doctor/patient accounts are remembered in the context so
 * DoctorSeeder/PatientSeeder reuse the same names and user_ids.
 */
class UserSeeder extends Seeder
{
    public function run(SeedContext $ctx): void
    {
        $rng = $ctx->rng('users');
        $p = $ctx->profile;
        $opened = intdiv(gmmktime(0, 0, 0, 1, 1, 2019), SeedContext::DAY); // hospital opening; clinical data starts 2022
        $users = [];

        $person = function (?string $gender = null) use ($rng): array {
            $gender ??= $rng->chance(0.5) ? 'male' : 'female';
            $first = $rng->pick($gender === 'female' ? Catalog::FEMALE_FIRST_NAMES : Catalog::MALE_FIRST_NAMES);

            return [$first, $rng->pick(Catalog::LAST_NAMES), $gender];
        };

        for ($i = 0; $i < $p['admin_users']; $i++) {
            [$first, $last] = $person();
            $users[] = ['admin', $first, $last, 'hospital.test', ($opened + $rng->int(0, 30)) * SeedContext::DAY + $rng->int(32400, 61200)];
        }

        for ($i = 0; $i < $p['staff_users']; $i++) {
            [$first, $last] = $person();
            $day = $ctx->sampleDay($rng, $opened, $ctx->referenceDay, registration: true);
            $users[] = ['staff', $first, $last, 'hospital.test', $day * SeedContext::DAY + $rng->int(32400, 61200)];
        }

        for ($i = 0; $i < $p['doctors']; $i++) {
            [$first, $last, $gender] = $person($rng->chance(0.62) ? 'male' : 'female');
            $ts = ($opened + $rng->int(0, 400)) * SeedContext::DAY + $rng->int(32400, 61200);
            $users[] = ['doctor', $first, $last, 'hospital.test', $ts, $gender];
        }

        $accounts = (int) round($p['patients'] * $p['patient_account_ratio']);
        for ($i = 0; $i < $accounts; $i++) {
            $gender = $rng->chance(0.01) ? 'other' : null;
            [$first, $last, $g] = $person($gender === 'other' ? null : $gender);
            $regDay = $ctx->sampleDay($rng, $opened, $ctx->referenceDay, registration: true);
            $users[] = ['patient', $first, $last, $rng->pick(['example.com', 'example.com', 'example.org', 'example.net']),
                $regDay * SeedContext::DAY + $rng->int(0, 86399), $gender ?? $g, $regDay];
        }

        // Insert in signup order, as a real application would have.
        usort($users, fn ($a, $b) => $a[4] <=> $b[4]);

        $insert = $ctx->inserter('users');
        foreach ($users as $n => $user) {
            [$role, $first, $last, $domain, $ts] = $user;
            // "first.last<n>": the dot + number format never collides with API-created usernames.
            $username = strtolower($first.'.'.$last).($n + 1);
            $id = $rng->uuidAt($ts);

            $insert->add([
                'user_id' => $id,
                'username' => $username,
                'email' => $username.'@'.$domain,
                'password' => $ctx->passwordHash,
                'role' => $role,
                'remember_token' => null,
                'created_at' => SeedContext::datetime($ts),
                'updated_at' => SeedContext::datetime($ts),
            ]);

            if ($role === 'doctor') {
                $ctx->doctorPeople[] = ['user_id' => $id, 'first' => $first, 'last' => $last, 'gender' => $user[5], 'ts' => $ts];
            } elseif ($role === 'patient') {
                $ctx->patientPeople[] = ['user_id' => $id, 'first' => $first, 'last' => $last, 'gender' => $user[5], 'reg_day' => $user[6]];
            }
        }

        $this->command->line('  users: '.$insert->flush());
    }
}
