<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\AppointmentResource;
use App\Services\AppointmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AppointmentController extends ApiController
{
    public function __construct(protected AppointmentService $appointmentService)
    {
    }

    public function index()
    {
        return $this->success(
            AppointmentResource::collection($this->appointmentService->list()),
            'Appointments retrieved successfully.'
        );
    }

    public function show(string $id)
    {
        $appointment = $this->appointmentService->find($id);

        if (! $appointment) {
            return $this->error('Appointment not found.', 404);
        }

        return $this->success(new AppointmentResource($appointment), 'Appointment retrieved successfully.');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'doctor_id' => ['required', 'string', 'exists:doctors,doctor_id'],
            'patient_id' => ['required', 'string', 'exists:patients,patient_id'],
            'appointment_date' => ['required', 'date'],
            'appointment_time' => ['required', 'string', 'max:20'],
            'status' => ['nullable', 'string', 'in:scheduled,completed,cancelled,missed'],
            'reason' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed.', 422, $validator->errors()->toArray());
        }

        $appointment = $this->appointmentService->create($validator->validated());

        return $this->success(new AppointmentResource($appointment), 'Appointment created successfully.', 201);
    }

    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'doctor_id' => ['sometimes', 'required', 'string', 'exists:doctors,doctor_id'],
            'patient_id' => ['sometimes', 'required', 'string', 'exists:patients,patient_id'],
            'appointment_date' => ['sometimes', 'required', 'date'],
            'appointment_time' => ['sometimes', 'required', 'string', 'max:20'],
            'status' => ['nullable', 'string', 'in:scheduled,completed,cancelled,missed'],
            'reason' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed.', 422, $validator->errors()->toArray());
        }

        $appointment = $this->appointmentService->update($id, $validator->validated());

        if (! $appointment) {
            return $this->error('Appointment not found.', 404);
        }

        return $this->success(new AppointmentResource($appointment), 'Appointment updated successfully.');
    }

    public function destroy(string $id)
    {
        $deleted = $this->appointmentService->delete($id);

        if (! $deleted) {
            return $this->error('Appointment not found.', 404);
        }

        return $this->success(null, 'Appointment deleted successfully.');
    }
}
