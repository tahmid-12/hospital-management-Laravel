<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PatientResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'patient_id' => $this->patient_id,
            'user' => $this->whenLoaded('user', fn () => [
                'user_id' => $this->user->user_id,
                'username' => $this->user->username,
                'email' => $this->user->email,
            ]),
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'date_of_birth' => $this->date_of_birth,
            'gender' => $this->gender,
            'phone' => $this->phone,
            'address' => $this->address,
            'blood_group' => $this->blood_group,
            'emergency_contact' => $this->emergency_contact,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
