<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasUuids;

    protected $primaryKey = 'room_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'room_number',
        'room_type',
        'status',
    ];

    public function admissions()
    {
        return $this->hasMany(Admission::class, 'room_id', 'room_id');
    }
}
