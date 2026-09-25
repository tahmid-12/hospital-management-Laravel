<?php

namespace Database\Seeders;

use Database\Seeders\Support\Catalog;
use Database\Seeders\Support\SeedContext;
use Database\Seeders\Support\WeightedPicker;
use Illuminate\Database\Seeder;

/**
 * Most completed appointments produce a medical record written shortly after
 * the visit. Emergency/walk-in encounters add records with appointment_id = NULL.
 */
class MedicalRecordSeeder extends Seeder
{
    private const RECORDED_VISIT_SHARE = 0.93;

    private const WALK_IN_RATIO = 0.15;

    public function run(SeedContext $ctx): void
    {
        $rng = $ctx->rng('medical_records');
        $diagnoses = WeightedPicker::zipf(count(Catalog::DIAGNOSES), 1.0, $rng);
        $insert = $ctx->inserter('medical_records');

        $write = function (int $patient, int $doctor, ?string $appointmentId, int $ts) use ($ctx, $rng, $diagnoses, $insert) {
            $symptoms = [];
            for ($s = $rng->int(1, 3); $s > 0; $s--) {
                $symptoms[] = $rng->pick(Catalog::SYMPTOMS);
            }

            $insert->add([
                'medical_record_id' => $rng->uuidAt($ts),
                'patient_id' => $ctx->patientIds[$patient],
                'doctor_id' => $ctx->doctorIds[$doctor],
                'appointment_id' => $appointmentId,
                'diagnosis' => Catalog::DIAGNOSES[$diagnoses->pick($rng)],
                'symptoms' => ucfirst(implode(', ', array_unique($symptoms))),
                'treatment_notes' => $rng->chance(0.85) ? $rng->pick(Catalog::TREATMENT_NOTES) : null,
                'created_at' => SeedContext::datetime($ts),
                'updated_at' => SeedContext::datetime($rng->chance(0.1) ? min($ts + $rng->int(1, 5) * SeedContext::DAY, $ctx->referenceTs) : $ts),
            ]);
        };

        foreach ($ctx->visitTs as $visit => $visitTs) {
            if ($rng->chance(self::RECORDED_VISIT_SHARE)) {
                $write($ctx->visitPatient[$visit], $ctx->visitDoctor[$visit], $ctx->visitAppointmentId[$visit],
                    min($visitTs + $rng->int(600, 3600), $ctx->referenceTs));
            }
        }

        // Walk-ins: patients drawn from existing visits, so busy patients stay busy.
        $visits = count($ctx->visitTs);
        for ($i = (int) round($visits * self::WALK_IN_RATIO); $i > 0 && $visits > 0; $i--) {
            $patient = $ctx->visitPatient[$rng->int(0, $visits - 1)];
            $day = $ctx->sampleDay($rng, $ctx->patientRegDay[$patient], $ctx->referenceDay);
            if ($day !== null) {
                $write($patient, $ctx->doctorPicker->pick($rng), null, $day * SeedContext::DAY + $rng->int(0, 86399));
            }
        }

        $this->command->line('  medical_records: '.$insert->flush());
    }
}
