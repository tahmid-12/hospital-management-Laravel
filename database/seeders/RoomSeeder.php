<?php

namespace Database\Seeders;

use Database\Seeders\Support\Catalog;
use Database\Seeders\Support\SeedContext;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Rooms are created as available (5% under maintenance). AdmissionSeeder later
 * marks rooms with a current admission as occupied.
 */
class RoomSeeder extends Seeder
{
    public function run(SeedContext $ctx): void
    {
        $rng = $ctx->rng('rooms');
        $typeWeights = array_map(fn (array $t) => $t[0], Catalog::ROOM_TYPES);
        $taken = array_flip(DB::table('rooms')->pluck('room_number')->all());
        $insert = $ctx->inserter('rooms');
        $ts = gmmktime(9, 0, 0, 1, 1, 2019);
        $sequence = 0;

        while (count($ctx->roomIds) < $ctx->profile['rooms']) {
            // Block A-E, floors 1-9, rooms 01-20 => "C-512"
            $number = chr(65 + intdiv($sequence, 180)).'-'.(intdiv($sequence % 180, 20) + 1).str_pad((string) ($sequence % 20 + 1), 2, '0', STR_PAD_LEFT);
            $sequence++;
            if (isset($taken[$number])) {
                continue;
            }

            $type = $rng->weighted($typeWeights);
            $id = $rng->uuidAt($ts);

            $insert->add([
                'room_id' => $id,
                'room_number' => $number,
                'room_type' => $type,
                'status' => $rng->chance(0.05) ? 'maintenance' : 'available',
                'created_at' => SeedContext::datetime($ts),
                'updated_at' => SeedContext::datetime($ts),
            ]);

            $ctx->roomIds[] = $id;
            $ctx->roomTypes[] = $type;
            $ctx->roomRate[] = Catalog::ROOM_TYPES[$type][1] * 100;
        }

        $this->command->line('  rooms: '.$insert->flush());
    }
}
