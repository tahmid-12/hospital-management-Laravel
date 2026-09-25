<?php

namespace Database\Seeders;

use Database\Seeders\Support\Catalog;
use Database\Seeders\Support\SeedContext;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Hospital stays. Sicker (more active) patients are admitted far more often.
 * A patient's stays never overlap, discharge is always after admission, and
 * stays still running on the reference date remain 'admitted' with no discharge.
 */
class AdmissionSeeder extends Seeder
{
    /** activity => [chance of ever being admitted, min stays, max stays] */
    private const STAYS = [
        'none' => [0.25, 1, 2],
        'low' => [0.50, 1, 3],
        'regular' => [0.80, 1, 6],
        'frequent' => [0.95, 2, 10],
        'chronic' => [1.00, 8, 30],
    ];

    public function run(SeedContext $ctx): void
    {
        $rng = $ctx->rng('admissions');
        $roomsByType = [];
        foreach ($ctx->roomTypes as $room => $type) {
            $roomsByType[$type][] = $room;
        }
        $typeWeights = array_intersect_key(array_map(fn (array $t) => $t[0], Catalog::ROOM_TYPES), $roomsByType);
        $insert = $ctx->inserter('admissions');
        $occupiedRooms = [];

        foreach ($ctx->patientIds as $patient => $patientId) {
            [$chance, $min, $max] = self::STAYS[$ctx->patientActivity[$patient]];
            if (! $rng->chance($chance)) {
                continue;
            }

            $days = [];
            for ($n = $rng->int($min, $max); $n > 0; $n--) {
                $days[] = $ctx->sampleDay($rng, $ctx->patientRegDay[$patient], $ctx->referenceDay);
            }
            sort($days);

            $previousEnd = 0;
            foreach (array_filter($days, fn ($d) => $d !== null) as $day) {
                $type = $rng->weighted($typeWeights);
                $room = $rng->pick($roomsByType[$type]);
                $start = max($day * SeedContext::DAY + $rng->int(0, 86399), $previousEnd + $rng->int(1, 20) * SeedContext::DAY);
                if ($start > $ctx->referenceTs) {
                    break;
                }

                // Log-normal length of stay around the room type's median, capped at 60 days.
                $stay = (int) min(60 * SeedContext::DAY, round(Catalog::ROOM_TYPES[$type][2] * exp($rng->normal() * 0.7) * SeedContext::DAY));
                $end = $start + max(4 * 3600, $stay);
                $previousEnd = $end;
                $ongoing = $end > $ctx->referenceTs;

                $insert->add([
                    'admission_id' => $rng->uuidAt($start),
                    'patient_id' => $patientId,
                    'room_id' => $ctx->roomIds[$room],
                    'admitted_at' => SeedContext::datetime($start),
                    'discharged_at' => $ongoing ? null : SeedContext::datetime($end),
                    'status' => $ongoing ? 'admitted' : ($rng->chance(0.03) ? 'transferred' : 'discharged'),
                    'notes' => $rng->chance(0.7) ? $rng->pick(Catalog::ADMISSION_REASONS) : null,
                    'created_at' => SeedContext::datetime($start),
                    'updated_at' => SeedContext::datetime($ongoing ? $start : $end),
                ]);

                $ctx->admissionPatient[] = $patient;
                $ctx->admissionRoom[] = $room;
                $ctx->admissionStart[] = $start;
                $ctx->admissionEnd[] = $ongoing ? null : $end;

                if ($ongoing) {
                    $occupiedRooms[$ctx->roomIds[$room]] = true;
                }
            }
        }

        $inserted = $insert->flush();

        // One set-based UPDATE instead of one query per room.
        foreach (array_chunk(array_keys($occupiedRooms), 1000) as $roomIds) {
            DB::table('rooms')->whereIn('room_id', $roomIds)->update(['status' => 'occupied']);
        }

        $this->command->line("  admissions: {$inserted} (".count($occupiedRooms).' rooms currently occupied)');
    }
}
