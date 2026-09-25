<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MedicineResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'medicine_id' => $this->medicine_id,
            'name' => $this->name,
            'description' => $this->description,
            'dosage_form' => $this->dosage_form,
            'strength' => $this->strength,
            'stock_quantity' => $this->stock_quantity,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
