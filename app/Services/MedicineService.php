<?php

namespace App\Services;

use App\Models\Medicine;
use Illuminate\Support\Str;

class MedicineService
{
    public function list()
    {
        return Medicine::orderBy('name')->get();
    }

    public function find(string $id): ?Medicine
    {
        return Medicine::find($id);
    }

    public function create(array $data): Medicine
    {
        $data['medicine_id'] = $data['medicine_id'] ?? (string) Str::uuid();

        return Medicine::create($data);
    }

    public function update(string $id, array $data): ?Medicine
    {
        $medicine = Medicine::find($id);

        if (! $medicine) {
            return null;
        }

        $medicine->fill($data);
        $medicine->save();

        return $medicine;
    }

    public function delete(string $id): bool
    {
        return (bool) Medicine::destroy($id);
    }
}
