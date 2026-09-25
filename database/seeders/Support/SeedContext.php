<?php

namespace Database\Seeders\Support;

use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;

/**
 * Shared state for one seeding run.
 *
 * Parent seeders record the IDs they generated here, and child seeders read
 * them back. Foreign keys therefore always point at rows inserted earlier in
 * the same run, and no seeder has to re-query the database for parent IDs.
 * Related rows are stored as parallel arrays of plain ints/strings (not
 * models) to keep memory small with hundreds of thousands of rows.
 */
final class SeedContext
{
    public const DAY = 86400;

    public string $profileName;

    public array $profile;

    public int $seed;

    public int $chunkSize;

    /** Days since 1970-01-01 of the reference "today". */
    public int $referenceDay;

    /** Last second of the reference day. Nothing except future appointments happens after it. */
    public int $referenceTs;

    /** Last day appointments may be booked for (end of the last weighted year). */
    public int $lastFutureDay;

    public string $passwordHash;

    // Departments
    public array $departmentIds = [];

    public array $departmentNames = [];

    // People planned by UserSeeder and turned into doctors/patients later.
    /** @var array<int, array{user_id: string, first: string, last: string, gender: string, ts: int}> */
    public array $doctorPeople = [];

    /** @var array<int, array{user_id: string, first: string, last: string, gender: string, reg_day: int}> */
    public array $patientPeople = [];

    // Doctors
    public array $doctorIds = [];

    public array $doctorDepartment = [];

    public array $doctorFee = []; // cents

    public ?WeightedPicker $doctorPicker = null;

    // Patients
    public array $patientIds = [];

    public array $patientRegDay = [];

    public array $patientActivity = [];

    public array $patientPrimaryDoctor = [];

    // Medicines
    public array $medicineIds = [];

    public array $medicineNames = [];

    public array $medicineForms = [];

    public array $medicinePrice = []; // cents per unit

    public ?WeightedPicker $medicinePicker = null;

    // Rooms
    public array $roomIds = [];

    public array $roomTypes = [];

    public array $roomRate = []; // cents per day

    // Completed appointments that happened (the basis for records, prescriptions, bills).
    public array $visitAppointmentId = [];

    public array $visitPatient = [];

    public array $visitDoctor = [];

    public array $visitTs = [];

    // Prescriptions
    public array $prescriptionIds = [];

    public array $prescriptionPatient = [];

    public array $prescriptionTs = [];

    public array $prescriptionRefillOf = []; // index of original prescription, or -1

    public array $prescriptionItems = [];    // "medicineIndex:quantity;..." filled by PrescriptionItemSeeder

    // Admissions
    public array $admissionPatient = [];

    public array $admissionRoom = [];

    public array $admissionStart = [];

    public array $admissionEnd = []; // null while still admitted

    // Bills
    public array $billIds = [];

    public array $billKind = [];

    public array $billRef = [];

    public array $billTs = [];

    public array $billStatus = [];

    public array $billTotal = []; // cents, filled by BillItemSeeder

    /** @var array<int, array{0: int, 1: int, 2: float}> year => [firstDay, lastDay, weight] */
    private array $yearRanges = [];

    /** @var array<int, array{0: int, 1: int, 2: float}> */
    private array $registrationYearRanges = [];

    public static function fromConfig(): self
    {
        $config = config('seeding');
        $context = new self;

        $context->profileName = $config['profile'];
        $context->profile = $config['profiles'][$context->profileName]
            ?? throw new InvalidArgumentException(
                "Unknown SEED_PROFILE '{$context->profileName}'. Use: ".implode(', ', array_keys($config['profiles']))
            );
        $context->seed = $config['random_seed'];
        $context->chunkSize = max(1, $config['insert_chunk_size']);

        $context->referenceDay = intdiv(strtotime($config['reference_date'].' 00:00:00 UTC'), self::DAY);
        $context->referenceTs = ($context->referenceDay + 1) * self::DAY - 1;
        $context->yearRanges = self::yearRanges($config['year_weights']);
        $context->registrationYearRanges = self::yearRanges($config['registration_year_weights']);
        $context->lastFutureDay = max(array_column($context->yearRanges, 1));

        // bcrypt is deliberately slow (~50-100 ms). Hash once and reuse it for every seeded user.
        $context->passwordHash = Hash::make('password');

        return $context;
    }

    /** Independent, reproducible random stream per seeder. */
    public function rng(string $name): Rng
    {
        return new Rng($this->seed + crc32($name));
    }

    public function inserter(string $table): BulkInserter
    {
        return new BulkInserter($table, $this->chunkSize);
    }

    /**
     * A day between $minDay and $maxDay following the configured year weights,
     * with fewer visits on Fridays (the weekend in Bangladesh). Null if no weighted year overlaps.
     */
    public function sampleDay(Rng $rng, int $minDay, int $maxDay, bool $registration = false): ?int
    {
        $weights = [];
        $bounds = [];

        foreach ($registration ? $this->registrationYearRanges : $this->yearRanges as $year => [$first, $last, $weight]) {
            $from = max($first, $minDay);
            $to = min($last, $maxDay);
            if ($from <= $to) {
                $weights[$year] = $weight;
                $bounds[$year] = [$from, $to];
            }
        }

        if ($weights === []) {
            return null;
        }

        [$from, $to] = $bounds[$rng->weighted($weights)];

        for ($try = 0; $try < 3; $try++) {
            $day = $rng->int($from, $to);
            // 1970-01-01 was a Thursday, so (day + 3) % 7 === 4 is a Friday.
            if (($day + 3) % 7 !== 4 || $rng->chance(0.25)) {
                break;
            }
        }

        return $day;
    }

    public static function date(int $ts): string
    {
        return gmdate('Y-m-d', $ts);
    }

    public static function datetime(int $ts): string
    {
        return gmdate('Y-m-d H:i:s', $ts);
    }

    public static function money(int $cents): string
    {
        return intdiv($cents, 100).'.'.str_pad((string) ($cents % 100), 2, '0', STR_PAD_LEFT);
    }

    private static function yearRanges(array $weights): array
    {
        $ranges = [];
        foreach ($weights as $year => $weight) {
            $ranges[(int) $year] = [
                intdiv(gmmktime(0, 0, 0, 1, 1, (int) $year), self::DAY),
                intdiv(gmmktime(0, 0, 0, 12, 31, (int) $year), self::DAY),
                (float) $weight,
            ];
        }

        return $ranges;
    }
}
