<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $fillable = ['name', 'code', 'unit_head'];

    function jobOrders()
    {
        return $this->hasMany(JobOrder::class);
    }

    public function unitHead()
    {
        return $this->belongsTo(User::class, 'unit_head');
    }
}
