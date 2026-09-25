<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Bill extends Model
{
    use HasUuids;

    protected $primaryKey = 'bill_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'patient_id',
        'bill_date',
        'total_amount',
        'status',
    ];

    protected $casts = [
        'bill_date' => 'date',
        'total_amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id', 'patient_id');
    }

    public function items()
    {
        return $this->hasMany(BillItem::class, 'bill_id', 'bill_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'bill_id', 'bill_id');
    }
}
