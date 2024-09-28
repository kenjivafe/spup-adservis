<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EquipmentBrand extends Model
{
    protected $fillable = ['name', 'code', 'equipment_category_id'];

    public function equipmentCategory()
    {
        return $this->belongsTo(EquipmentCategory::class);
    }

    public function equipmentTypes()
    {
        return $this->hasMany(EquipmentType::class);
    }

    public function equipment()
    {
        return $this->hasMany(Equipment::class);
    }
}
