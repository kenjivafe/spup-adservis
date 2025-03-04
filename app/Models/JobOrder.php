<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Spatie\Permission\Traits\HasRoles;

class JobOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_order_title',
        'unit_name',
        'date_requested',
        'date_needed',
        'particulars',
        'materials',
        'requested_by',
        'canceled_by',
        'canceled_at',
        'cancelation_reason',
        'recommended_by',
        'recommended_at',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
        'approved_by',
        'approved_at',
        'assigned_role',
        'assigned_to',
        'accomplished_by',
        'accomplished_at',
        'checked_by',
        'checked_at',
        'confirmed_by',
        'confirmed_at',
        'status',
        'date_begun',
        'date_completed',
    ];

    protected $dates = [
        'date_requested',
        'date_needed',
        'date_begun',
        'date_completed',
        'recommended_at',
        'approved_at',
        'accomplished_at',
        'checked_at',
        'confirmed_at',
        'rejected_at',
        'canceled_at',
    ];

    protected $casts = [
        'date_requested' => 'datetime',
        'date_needed'    => 'datetime',
        'date_begun'     => 'datetime',
        'date_completed' => 'datetime',
        'recommended_at' => 'datetime',
        'approved_at' => 'datetime',
        'accomplished_at' => 'datetime',
        'checked_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'rejected_at' => 'datetime',
        'canceled_at' => 'datetime',
    ];

    public function hasPendingAction()
    {
        // Example condition: job order is pending and assigned to the current user
        return (
            optional($this)->status === 'Pending' &&
            empty($this->assigned_to) &&
            empty($this->recommended_by) &&
            auth()->user()->can('Recommend Job Orders') &&
            !auth()->user()->hasRole('Admin')
        )||(
            optional($this)->status === 'Pending' &&
            !empty($this->recommended_by) &&
            auth()->user()->can('Manage Job Orders')
        )||(
            $this->status === 'Assigned' &&
            !empty($this->approved_by) &&
            empty($this->accomplished_by) &&
            auth()->user()->id == $this->assigned_to
        )||(
            $this->status === 'Assigned' &&
            empty($this->checked_by) &&
            !empty($this->accomplished_by) &&
            auth()->user()->id == $this->recommended_by
        )||(
            $this->status === 'Completed' &&
            empty($this->confirmed_by) &&
            !empty($this->checked_by) &&
            auth()->user()->id == $this->requested_by
        );
    }


    public function user()
    {
        return $this->belongsTo(User::class); // Assuming a belongsTo relationship
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class); // Assuming a belongsTo relationship
    }

    public function jobOrderEquipments()
    {
        return $this->hasMany(JobOrderEquipment::class);
    }

    public function equipment()
    {
        return $this->belongsToMany(Equipment::class, 'equipment');
    }

    // Assuming User model is used to store user information
    public function requestedBy() {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function rejectedBy() {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function canceledBy() {
        return $this->belongsTo(User::class, 'canceled_by');
    }

    public function recommendedBy() {
        return $this->belongsTo(User::class, 'recommended_by');
    }

    public function approvedBy() {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function assignedTo() {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function accomplishedBy() {
        return $this->belongsTo(User::class, 'accomplished_by');
    }

    public function checkedBy() {
        return $this->belongsTo(User::class, 'checked_by');
    }

    public function confirmedBy() {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

}
