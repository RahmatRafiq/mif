<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Order extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $table = 'master_orders';

    protected $fillable = [
        'order_number',
        'product_name',
        'product_code',
        'qty_total',
        'customer',
        'order_date',
        'due_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'qty_total' => 'integer',
        'order_date' => 'date',
        'due_date' => 'date',
    ];

    /**
     * Get the options for activity logging.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['order_number', 'product_name', 'product_code', 'qty_total', 'customer', 'status'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn (string $eventName) => "Order {$this->order_number} (ID: {$this->id}) was {$eventName}");
    }

    /**
     * Relationship: Order has many Schedules
     */
    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'order_id');
    }

    /**
     * Get total scheduled quantity
     */
    public function getTotalScheduledQtyAttribute()
    {
        return $this->schedules()->sum('qty_total_target');
    }

    /**
     * Get total completed quantity
     */
    public function getTotalCompletedQtyAttribute()
    {
        return $this->schedules()->sum('qty_completed');
    }

    /**
     * Get remaining quantity to be scheduled
     */
    public function getRemainingQtyAttribute()
    {
        return $this->qty_total - $this->total_scheduled_qty;
    }

    /**
     * Check if order is fully scheduled
     */
    public function isFullyScheduled()
    {
        return $this->remaining_qty <= 0;
    }

    /**
     * Check if order is completed
     */
    public function isCompleted()
    {
        return $this->total_completed_qty >= $this->qty_total;
    }

    /**
     * Scope: Pending orders (not scheduled)
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope: Orders that can be scheduled
     */
    public function scopeSchedulable($query)
    {
        return $query->whereIn('status', ['pending', 'scheduled'])
            ->whereDate('due_date', '>=', now());
    }
}
