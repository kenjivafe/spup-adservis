<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'venue_id',
        'person_responsible',
        'unit_id',
        'participants',
        'purpose',
        'starts_at',
        'ends_at',
        'actual_started_at',
        'actual_ended_at',
        'status',
        'fund_source',
        'specifics',
        'date_requested',
        'noted_by',
        'noted_at',
        'approved_by',
        'approved_at',
        'approved_by_finance',
        'approved_by_finance_at',
        'received_by',
        'received_at',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
        'canceled_by',
        'canceled_at',
        'cancelation_reason',
    ];

    protected $dates = [
        'date_requested',
    ];

    protected $casts = [
        'date_requested' => 'datetime',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'actual_started_at' => 'datetime',
        'actual_ended_at' => 'datetime',
        'noted_at' => 'datetime',
        'approved_at' => 'datetime',
        'approved_by_finance_at' => 'datetime',
        'received_at' => 'datetime',
        'rejected_at' => 'datetime',
        'canceled_at' => 'datetime',
    ];

    public function hasPendingAction()
    {
        // Example condition: job order is pending and assigned to the current user
        return (
            $this->status === 'Pending' &&
            auth()->user()->id === ($this->unit)->unitHead->id &&
            empty($this->noted_by)
        )||(
            $this->status === 'Pending' &&
            auth()->user()->can('Manage Venue Bookings') &&
            !empty($this->noted_by) &&
            empty($this->approved_by)
        )||(
            optional($this)->status === 'Pending' &&
            is_null($this->approved_by_finance) &&
            !is_null($this->approved_by) &&
            is_null($this->rejected_by) &&
            auth()->user()->can('Approve Venue Bookings as Finance') &&
            !auth()->user()->hasRole('Admin')
        )||(
            ($this)->status === 'Approved' &&
            !empty($this->approved_by_finance) &&
            is_null($this->received_by) &&
            auth()->user()->id === ($this->venue)->facilitator
        );
    }

    public function personResponsible()
    {
        return $this->belongsTo(User::class, 'person_responsible'); // Assuming a belongsTo relationship
    }

    public function notedBy()
    {
        return $this->belongsTo(User::class, 'noted_by'); // Assuming a belongsTo relationship
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by'); // Assuming a belongsTo relationship
    }

    public function approvedByFinance()
    {
        return $this->belongsTo(User::class, 'approved_by_finance');
    }

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function canceledBy()
    {
        return $this->belongsTo(User::class, 'canceled_by');
    }

    public function rejectedBy()
    {
        return $this->belongsTo(User::class, 'rejected_by'); // Assuming a belongsTo relationship
    }

    public function venue()
    {
        return $this->belongsTo(Venue::class); // Adjust model name if different
    }

    public function venueHead()
    {
        return $this->belongsTo(User::class, 'facilitator');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function unitHead()
    {
        return $this->belongsTo(User::class, 'unit_head');
    }
}
