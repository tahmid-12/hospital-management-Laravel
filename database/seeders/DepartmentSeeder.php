<?php

namespace Database\Seeders;

use Database\Seeders\Support\Catalog;
use Database\Seeders\Support\SeedContext;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentSeeder extends Seeder
{
    public function run(SeedContext $ctx): void
    {
        $rng = $ctx->rng('departments');
        $names = array_slice(array_keys(Catalog::DEPARTMENTS), 0, $ctx->profile['departments']);

        // department_name is unique: reuse departments that already exist instead of failing.
        $existing = DB::table('departments')->whereIn('department_name', $names)->pluck('department_id', 'department_name');
        $rows = [];

        foreach ($names as $name) {
            $id = $existing[$name] ?? null;

            if ($id === null) {
                $ts = gmmktime(9, 0, 0, 1, 1, 2019) + $rng->int(0, 180) * SeedContext::DAY;
                $id = $rng->uuidAt($ts);
                $rows[] = [
                    'department_id' => $id,
                    'department_name' => $name,
                    'description' => Catalog::DEPARTMENTS[$name][0],
                    'created_at' => SeedContext::datetime($ts),
                    'updated_at' => SeedContext::datetime($ts),
                ];
            }

            $ctx->departmentIds[] = $id;
            $ctx->departmentNames[] = $name;
        }

        DB::table('departments')->insert($rows);

        $this->command->line('  departments: '.count($rows).' inserted, '.count($existing).' existing reused');
    }
}
