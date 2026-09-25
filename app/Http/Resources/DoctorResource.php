<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DoctorResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'doctor_id' => $this->doctor_id,
            'user' => $this->whenLoaded('user', fn () => [
                'user_id' => $this->user->user_id,
                'username' => $this->user->username,
                'email' => $this->user->email,
                'role' => $this->user->role,
            ]),
            'department' => $this->whenLoaded('department', fn () => [
                'department_id' => $this->department->department_id,
                'department_name' => $this->department->department_name,
            ]),
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'phone' => $this->phone,
            'specialization' => $this->specialization,
            'license_number' => $this->license_number,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
