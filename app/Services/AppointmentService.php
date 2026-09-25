<?php

namespace App\Services;

use App\Models\Appointment;
use Illuminate\Support\Str;

class AppointmentService
{
    public function list()
    {
        return Appointment::with(['doctor.user', 'patient.user'])->orderBy('appointment_date')->get();
    }

    public function find(string $id): ?Appointment
    {
        return Appointment::with(['doctor.user', 'patient.user'])->find($id);
    }

    public function create(array $data): Appointment
    {
        $data['appointment_id'] = $data['appointment_id'] ?? (string) Str::uuid();

        return Appointment::create($data);
    }

    public function update(string $id, array $data): ?Appointment
    {
        $appointment = Appointment::find($id);

        if (! $appointment) {
            return null;
        }

        $appointment->fill($data);
        $appointment->save();

        return $appointment->fresh(['doctor.user', 'patient.user']);
    }

    public function delete(string $id): bool
    {
        return (bool) Appointment::destroy($id);
    }
}
