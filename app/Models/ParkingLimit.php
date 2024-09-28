<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParkingLimit extends Model
{
    use HasFactory;

    protected $fillable = ['department_id', 'vehicle_category', 'limit'];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
