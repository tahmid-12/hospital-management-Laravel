<?php

namespace Database\Seeders;

use Database\Seeders\Support\Catalog;
use Database\Seeders\Support\SeedContext;
use Illuminate\Database\Seeder;

/**
 * 0-3 prescriptions per completed visit, written during the visit.
 * Frequent and chronic patients also get monthly refills (appointment_id = NULL)
 * that repeat the original medicines, which concentrates prescriptions on a
 * small group of patients.
 */
class PrescriptionSeeder extends Seeder
{
    private const PER_VISIT = [0 => 12, 1 => 42, 2 => 30, 3 => 16];

    private const REFILLS = [0 => 25, 1 => 35, 2 => 40];

    public function run(SeedContext $ctx): void
    {
        $rng = $ctx->rng('prescriptions');
        $insert = $ctx->inserter('prescriptions');

        $write = function (int $patient, int $doctor, ?string $appointmentId, int $ts, int $refillOf) use ($ctx, $rng, $insert): int {
            $id = $rng->uuidAt($ts);
            $insert->add([
                'prescription_id' => $id,
                'patient_id' => $ctx->patientIds[$patient],
                'doctor_id' => $ctx->doctorIds[$doctor],
                'appointment_id' => $appointmentId,
                'prescribed_at' => SeedContext::datetime($ts),
                'notes' => $rng->chance(0.3) ? $rng->pick(Catalog::PRESCRIPTION_NOTES) : null,
                'created_at' => SeedContext::datetime($ts),
                'updated_at' => SeedContext::datetime($ts),
            ]);

            $ctx->prescriptionIds[] = $id;
            $ctx->prescriptionPatient[] = $patient;
            $ctx->prescriptionTs[] = $ts;
            $ctx->prescriptionRefillOf[] = $refillOf;

            return count($ctx->prescriptionIds) - 1;
        };

        foreach ($ctx->visitTs as $visit => $visitTs) {
            $patient = $ctx->visitPatient[$visit];
            $doctor = $ctx->visitDoctor[$visit];
            $first = -1;

            for ($n = $rng->weighted(self::PER_VISIT); $n > 0; $n--) {
                $index = $write($patient, $doctor, $ctx->visitAppointmentId[$visit], min($visitTs + $rng->int(300, 3000), $ctx->referenceTs), -1);
                $first = $first < 0 ? $index : $first;
            }

            if ($first >= 0 && in_array($ctx->patientActivity[$patient], ['frequent', 'chronic'], true)) {
                $refillTs = $visitTs;
                for ($r = $rng->weighted(self::REFILLS); $r > 0; $r--) {
                    $refillTs += $rng->int(28, 35) * SeedContext::DAY;
                    if ($refillTs > $ctx->referenceTs) {
                        break;
                    }
                    $write($patient, $doctor, null, $refillTs, $first);
                }
            }
        }

        $this->command->line('  prescriptions: '.$insert->flush());
    }
}
