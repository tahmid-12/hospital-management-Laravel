<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\DepartmentResource;
use App\Services\DepartmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class DepartmentController extends ApiController
{
    public function __construct(protected DepartmentService $departmentService)
    {
    }

    public function index()
    {
        $departments = $this->departmentService->list();

        return $this->success(
            DepartmentResource::collection($departments),
            'Departments retrieved successfully.'
        );
    }

    public function show(string $id)
    {
        $department = $this->departmentService->find($id);

        if (! $department) {
            return $this->error('Department not found.', 404);
        }

        return $this->success(new DepartmentResource($department), 'Department retrieved successfully.');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'department_name' => ['required', 'string', 'max:255', 'unique:departments,department_name'],
            'description' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed.', 422, $validator->errors()->toArray());
        }

        $department = $this->departmentService->create($validator->validated());

        return $this->success(new DepartmentResource($department), 'Department created successfully.', 201);
    }

    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'department_name' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('departments', 'department_name')->ignore($id, 'department_id')],
            'description' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed.', 422, $validator->errors()->toArray());
        }

        $department = $this->departmentService->update($id, $validator->validated());

        if (! $department) {
            return $this->error('Department not found.', 404);
        }

        return $this->success(new DepartmentResource($department), 'Department updated successfully.');
    }

    public function destroy(string $id)
    {
        $deleted = $this->departmentService->delete($id);

        if (! $deleted) {
            return $this->error('Department not found.', 404);
        }

        return $this->success(null, 'Department deleted successfully.');
    }
}
