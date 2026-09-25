<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\PatientResource;
use App\Services\PatientService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PatientController extends ApiController
{
    public function __construct(protected PatientService $patientService)
    {
    }

    public function index()
    {
        return $this->success(
            PatientResource::collection($this->patientService->list()),
            'Patients retrieved successfully.'
        );
    }

    public function show(string $id)
    {
        $patient = $this->patientService->find($id);

        if (! $patient) {
            return $this->error('Patient not found.', 404);
        }

        return $this->success(new PatientResource($patient), 'Patient retrieved successfully.');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => ['required', 'string', 'exists:users,user_id'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'date_of_birth' => ['required', 'date'],
            'gender' => ['nullable', 'string', 'in:male,female,other'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
            'blood_group' => ['nullable', 'string', 'max:10'],
            'emergency_contact' => ['nullable', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed.', 422, $validator->errors()->toArray());
        }

        $patient = $this->patientService->create($validator->validated());

        return $this->success(new PatientResource($patient), 'Patient created successfully.', 201);
    }

    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => ['sometimes', 'required', 'string', 'exists:users,user_id'],
            'first_name' => ['sometimes', 'required', 'string', 'max:255'],
            'last_name' => ['sometimes', 'required', 'string', 'max:255'],
            'date_of_birth' => ['sometimes', 'required', 'date'],
            'gender' => ['nullable', 'string', 'in:male,female,other'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
            'blood_group' => ['nullable', 'string', 'max:10'],
            'emergency_contact' => ['nullable', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed.', 422, $validator->errors()->toArray());
        }

        $patient = $this->patientService->update($id, $validator->validated());

        if (! $patient) {
            return $this->error('Patient not found.', 404);
        }

        return $this->success(new PatientResource($patient), 'Patient updated successfully.');
    }

    public function destroy(string $id)
    {
        $deleted = $this->patientService->delete($id);

        if (! $deleted) {
            return $this->error('Patient not found.', 404);
        }

        return $this->success(null, 'Patient deleted successfully.');
    }
}
