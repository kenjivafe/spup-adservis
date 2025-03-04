<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class ParkingStickerApplication extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'vehicle_id',
        'applicant_id',
        'department_id',
        'parking_type',
        'vehicle_color',
        'plate_number',
        'contact_number',
        'signature',
        'orcr_attachment',
        'assessment_attachment',
        'status',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
        'revoked_by',
        'revoked_at',
        'revocation_reason',
        'expiration_date'
    ];

    public function applicant()
    {
        return $this->belongsTo(User::class, 'applicant_id');
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    protected $casts = [
        'expiration_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'recommended_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'revoked_at' => 'datetime',
        // other datetime fields as necessary
    ];
}
