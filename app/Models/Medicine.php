<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Medicine extends Model
{
    use HasUuids;

    protected $primaryKey = 'medicine_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'description',
        'dosage_form',
        'strength',
        'stock_quantity',
    ];

    public function prescriptionItems()
    {
        return $this->hasMany(PrescriptionItem::class, 'medicine_id', 'medicine_id');
    }
}
