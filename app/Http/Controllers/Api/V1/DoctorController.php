<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\DoctorResource;
use App\Services\DoctorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class DoctorController extends ApiController
{
    public function __construct(protected DoctorService $doctorService)
    {
    }

    public function index()
    {
        return $this->success(
            DoctorResource::collection($this->doctorService->list()),
            'Doctors retrieved successfully.'
        );
    }

    public function show(string $id)
    {
        $doctor = $this->doctorService->find($id);

        if (! $doctor) {
            return $this->error('Doctor not found.', 404);
        }

        return $this->success(new DoctorResource($doctor), 'Doctor retrieved successfully.');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => ['required', 'string', 'exists:users,user_id'],
            'department_id' => ['required', 'string', 'exists:departments,department_id'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'specialization' => ['nullable', 'string', 'max:255'],
            'license_number' => ['required', 'string', 'max:255', 'unique:doctors,license_number'],
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed.', 422, $validator->errors()->toArray());
        }

        $doctor = $this->doctorService->create($validator->validated());

        return $this->success(new DoctorResource($doctor), 'Doctor created successfully.', 201);
    }

    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => ['sometimes', 'required', 'string', 'exists:users,user_id'],
            'department_id' => ['sometimes', 'required', 'string', 'exists:departments,department_id'],
            'first_name' => ['sometimes', 'required', 'string', 'max:255'],
            'last_name' => ['sometimes', 'required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'specialization' => ['nullable', 'string', 'max:255'],
            'license_number' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('doctors', 'license_number')->ignore($id, 'doctor_id')],
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed.', 422, $validator->errors()->toArray());
        }

        $doctor = $this->doctorService->update($id, $validator->validated());

        if (! $doctor) {
            return $this->error('Doctor not found.', 404);
        }

        return $this->success(new DoctorResource($doctor), 'Doctor updated successfully.');
    }

    public function destroy(string $id)
    {
        $deleted = $this->doctorService->delete($id);

        if (! $deleted) {
            return $this->error('Doctor not found.', 404);
        }

        return $this->success(null, 'Doctor deleted successfully.');
    }
}
