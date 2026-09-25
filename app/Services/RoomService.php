<?php

namespace App\Services;

use App\Models\Room;
use Illuminate\Support\Str;

class RoomService
{
    public function list()
    {
        return Room::orderBy('room_number')->get();
    }

    public function find(string $id): ?Room
    {
        return Room::find($id);
    }

    public function create(array $data): Room
    {
        $data['room_id'] = $data['room_id'] ?? (string) Str::uuid();

        return Room::create($data);
    }

    public function update(string $id, array $data): ?Room
    {
        $room = Room::find($id);

        if (! $room) {
            return null;
        }

        $room->fill($data);
        $room->save();

        return $room;
    }

    public function delete(string $id): bool
    {
        return (bool) Room::destroy($id);
    }
}
