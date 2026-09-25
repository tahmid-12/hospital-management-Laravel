<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'appointment_id' => $this->appointment_id,
            'doctor' => $this->whenLoaded('doctor', fn () => [
                'doctor_id' => $this->doctor->doctor_id,
                'name' => trim(($this->doctor->first_name ?? '').' '.($this->doctor->last_name ?? '')),
            ]),
            'patient' => $this->whenLoaded('patient', fn () => [
                'patient_id' => $this->patient->patient_id,
                'name' => trim(($this->patient->first_name ?? '').' '.($this->patient->last_name ?? '')),
            ]),
            'appointment_date' => $this->appointment_date,
            'appointment_time' => $this->appointment_time,
            'status' => $this->status,
            'reason' => $this->reason,
            'notes' => $this->notes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
