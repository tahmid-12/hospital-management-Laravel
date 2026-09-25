<?php

namespace App\Services;

use App\Models\Department;

class DepartmentService
{
    public function list()
    {
        return Department::orderBy('department_name')->get();
    }

    public function find(string $id): ?Department
    {
        return Department::find($id);
    }

    public function create(array $data): Department
    {
        return Department::create($data);
    }

    public function update(string $id, array $data): ?Department
    {
        $department = Department::find($id);

        if (! $department) {
            return null;
        }

        $department->fill($data);
        $department->save();

        return $department;
    }

    public function delete(string $id): bool
    {
        return (bool) Department::destroy($id);
    }
}
