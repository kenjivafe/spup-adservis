<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Equipment extends Model
{
    protected $fillable = ['unit_id', 'equipment_category_id', 'equipment_type_id', 'equipment_brand_id', 'code', 'status', 'disposal_reason', 'date_acquired', 'date_disposed'];

    protected $dates = [
        'date_acquired',
        'date_disposed',
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function equipmentCategory()
    {
        return $this->belongsTo(EquipmentCategory::class);
    }

    public function equipmentType()
    {
        return $this->belongsTo(EquipmentType::class);
    }

    public function equipmentBrand()
    {
        return $this->belongsTo(EquipmentBrand::class);
    }

    public function jobOrderEquipments()
    {
        return $this->hasMany(JobOrderEquipment::class);
    }

    public function markAsInactive()
    {
        $this->update(['status' => 'Inactive']);
    }

    public function markAsActive()
    {
        $this->update(['status' => 'Active']);
    }
}
