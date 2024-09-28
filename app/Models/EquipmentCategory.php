<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EquipmentCategory extends Model
{
    protected $fillable = ['name', 'code'];

    public function equipmentTypes()
    {
        return $this->hasMany(EquipmentType::class);
    }

    public function equipmentBrands()
    {
        return $this->hasMany(EquipmentBrand::class);
    }

    public function equipment()
    {
        return $this->hasMany(Equipment::class);
    }
}

