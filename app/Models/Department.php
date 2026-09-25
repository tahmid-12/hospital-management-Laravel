<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasUuids;

    protected $primaryKey = 'department_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'department_name',
        'description',
    ];

    public function doctors()
    {
        return $this->hasMany(Doctor::class, 'department_id', 'department_id');
    }
}
