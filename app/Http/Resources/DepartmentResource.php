<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DepartmentResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'department_id' => $this->department_id,
            'department_name' => $this->department_name,
            'description' => $this->description,
            'doctor_count' => $this->whenLoaded('doctors', fn () => $this->doctors->count(), $this->doctors()->count()),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
