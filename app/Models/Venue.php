<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Venue extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'name',
        'venue_image',
        'capacity',
        'description',
        'facilitator'
    ];

    public function facilitator()
    {
        return $this->belongsTo(User::class, 'facilitator');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
