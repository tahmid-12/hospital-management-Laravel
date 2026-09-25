<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\MedicineResource;
use App\Services\MedicineService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class MedicineController extends ApiController
{
    public function __construct(protected MedicineService $medicineService)
    {
    }

    public function index()
    {
        return $this->success(
            MedicineResource::collection($this->medicineService->list()),
            'Medicines retrieved successfully.'
        );
    }

    public function show(string $id)
    {
        $medicine = $this->medicineService->find($id);

        if (! $medicine) {
            return $this->error('Medicine not found.', 404);
        }

        return $this->success(new MedicineResource($medicine), 'Medicine retrieved successfully.');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255', 'unique:medicines,name'],
            'description' => ['nullable', 'string'],
            'dosage_form' => ['nullable', 'string', 'max:100'],
            'strength' => ['nullable', 'string', 'max:100'],
            'stock_quantity' => ['nullable', 'integer', 'min:0'],
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed.', 422, $validator->errors()->toArray());
        }

        $medicine = $this->medicineService->create($validator->validated());

        return $this->success(new MedicineResource($medicine), 'Medicine created successfully.', 201);
    }

    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('medicines', 'name')->ignore($id, 'medicine_id')],
            'description' => ['nullable', 'string'],
            'dosage_form' => ['nullable', 'string', 'max:100'],
            'strength' => ['nullable', 'string', 'max:100'],
            'stock_quantity' => ['nullable', 'integer', 'min:0'],
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed.', 422, $validator->errors()->toArray());
        }

        $medicine = $this->medicineService->update($id, $validator->validated());

        if (! $medicine) {
            return $this->error('Medicine not found.', 404);
        }

        return $this->success(new MedicineResource($medicine), 'Medicine updated successfully.');
    }

    public function destroy(string $id)
    {
        $deleted = $this->medicineService->delete($id);

        if (! $deleted) {
            return $this->error('Medicine not found.', 404);
        }

        return $this->success(null, 'Medicine deleted successfully.');
    }
}
