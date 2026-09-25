<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\RoomResource;
use App\Services\RoomService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class RoomController extends ApiController
{
    public function __construct(protected RoomService $roomService)
    {
    }

    public function index()
    {
        return $this->success(
            RoomResource::collection($this->roomService->list()),
            'Rooms retrieved successfully.'
        );
    }

    public function show(string $id)
    {
        $room = $this->roomService->find($id);

        if (! $room) {
            return $this->error('Room not found.', 404);
        }

        return $this->success(new RoomResource($room), 'Room retrieved successfully.');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_number' => ['required', 'string', 'max:50', 'unique:rooms,room_number'],
            'room_type' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'string', 'in:available,occupied,maintenance'],
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed.', 422, $validator->errors()->toArray());
        }

        $room = $this->roomService->create($validator->validated());

        return $this->success(new RoomResource($room), 'Room created successfully.', 201);
    }

    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'room_number' => ['sometimes', 'required', 'string', 'max:50', Rule::unique('rooms', 'room_number')->ignore($id, 'room_id')],
            'room_type' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'string', 'in:available,occupied,maintenance'],
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed.', 422, $validator->errors()->toArray());
        }

        $room = $this->roomService->update($id, $validator->validated());

        if (! $room) {
            return $this->error('Room not found.', 404);
        }

        return $this->success(new RoomResource($room), 'Room updated successfully.');
    }

    public function destroy(string $id)
    {
        $deleted = $this->roomService->delete($id);

        if (! $deleted) {
            return $this->error('Room not found.', 404);
        }

        return $this->success(null, 'Room deleted successfully.');
    }
}
