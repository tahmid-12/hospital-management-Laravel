<?php

namespace Database\Seeders;

use Database\Seeders\Support\Catalog;
use Database\Seeders\Support\SeedContext;
use Illuminate\Database\Seeder;

/**
 * Each patient books a number of appointments based on their activity class
 * (none / low / regular / frequent / chronic), mostly with their usual doctor.
 * This is where the patient and doctor skew of the whole dataset comes from.
 *
 * Rows are inserted patient by patient, so the physical order of this table is
 * NOT chronological (unlike bills). Compare pg_stats.correlation on both.
 */
class AppointmentSeeder extends Seeder
{
    public function run(SeedContext $ctx): void
    {
        $rng = $ctx->rng('appointments');
        $activity = config('seeding.patient_activity');
        $statusWeights = config('seeding.appointment_status');
        $primaryShare = config('seeding.primary_doctor_share');
        $insert = $ctx->inserter('appointments');

        foreach ($ctx->patientIds as $patient => $patientId) {
            [$min, $max] = $activity[$ctx->patientActivity[$patient]]['appointments'];
            $count = $rng->int($min, $max);
            if ($count === 0) {
                continue;
            }

            // Recently registered patients have had less time to book appointments.
            $regDay = $ctx->patientRegDay[$patient];
            $yearsAvailable = ($ctx->lastFutureDay - $regDay) / 365;
            $count = max(1, (int) round($count * min(1.0, max(0.2, $yearsAvailable / 2.0))));

            for ($k = 0; $k < $count; $k++) {
                $day = $ctx->sampleDay($rng, $regDay, $ctx->lastFutureDay);
                if ($day === null) {
                    continue;
                }

                $doctor = $rng->chance($primaryShare) ? $ctx->patientPrimaryDoctor[$patient] : $ctx->doctorPicker->pick($rng);
                // Morning (09:00-13:00) and evening (16:00-21:00) chambers, 15-minute slots.
                $slot = $rng->chance(0.55) ? $rng->int(36, 51) : $rng->int(64, 83);
                $ts = $day * SeedContext::DAY + $slot * 900;
                $future = $ts > $ctx->referenceTs;
                $status = $rng->weighted($future ? $statusWeights['future'] : $statusWeights['past']);

                // Booked 0-21 days ahead, never before registration and never after "today".
                $bookedTs = min($ts - $rng->int(1800, 21 * SeedContext::DAY), $ctx->referenceTs - $rng->int(0, 7200));
                $bookedTs = max($bookedTs, $regDay * SeedContext::DAY);
                $updatedTs = match ($status) {
                    'completed', 'missed' => $ts + $rng->int(900, 5400),
                    'cancelled' => $bookedTs + (int) (($ts - $bookedTs) * $rng->float()),
                    default => $bookedTs,
                };

                $id = $rng->uuidAt($bookedTs);
                $insert->add([
                    'appointment_id' => $id,
                    'doctor_id' => $ctx->doctorIds[$doctor],
                    'patient_id' => $patientId,
                    'appointment_date' => SeedContext::date($ts),
                    'appointment_time' => gmdate('H:i:s', $ts),
                    'status' => $status,
                    'reason' => $rng->chance(0.9) ? $rng->pick(Catalog::APPOINTMENT_REASONS) : null,
                    'notes' => $status === 'completed' && $rng->chance(0.4) ? $rng->pick(Catalog::APPOINTMENT_NOTES) : null,
                    'created_at' => SeedContext::datetime($bookedTs),
                    'updated_at' => SeedContext::datetime(min($updatedTs, $ctx->referenceTs)),
                ]);

                if ($status === 'completed') {
                    $ctx->visitAppointmentId[] = $id;
                    $ctx->visitPatient[] = $patient;
                    $ctx->visitDoctor[] = $doctor;
                    $ctx->visitTs[] = $ts;
                }
            }
        }

        $this->command->line('  appointments: '.$insert->flush().' ('.count($ctx->visitTs).' completed)');
    }
}
