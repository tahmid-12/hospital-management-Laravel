<?php

namespace App\Services;

use App\Models\Patient;
use Illuminate\Support\Str;

class PatientService
{
    public function list()
    {
        return Patient::with(['user'])->orderBy('last_name')->get();
    }

    public function find(string $id): ?Patient
    {
        return Patient::with(['user'])->find($id);
    }

    public function create(array $data): Patient
    {
        $data['patient_id'] = $data['patient_id'] ?? (string) Str::uuid();

        return Patient::create($data);
    }

    public function update(string $id, array $data): ?Patient
    {
        $patient = Patient::find($id);

        if (! $patient) {
            return null;
        }

        $patient->fill($data);
        $patient->save();

        return $patient->fresh(['user']);
    }

    public function delete(string $id): bool
    {
        return (bool) Patient::destroy($id);
    }
}
