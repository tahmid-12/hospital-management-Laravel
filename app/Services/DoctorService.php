<?php

namespace App\Services;

use App\Models\Doctor;
use Illuminate\Support\Str;

class DoctorService
{
    public function list()
    {
        return Doctor::with(['user', 'department'])->orderBy('last_name')->get();
    }

    public function find(string $id): ?Doctor
    {
        return Doctor::with(['user', 'department'])->find($id);
    }

    public function create(array $data): Doctor
    {
        $data['doctor_id'] = $data['doctor_id'] ?? (string) Str::uuid();

        return Doctor::create($data);
    }

    public function update(string $id, array $data): ?Doctor
    {
        $doctor = Doctor::find($id);

        if (! $doctor) {
            return null;
        }

        $doctor->fill($data);
        $doctor->save();

        return $doctor->fresh(['user', 'department']);
    }

    public function delete(string $id): bool
    {
        return (bool) Doctor::destroy($id);
    }
}
