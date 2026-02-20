<?php

namespace App\Models;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Schedule extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'order_id',
        'line_id',
        'start_date',
        'finish_date',
        'current_finish_date',
        'qty_total_target',
        'qty_completed',
        'days_extended',
        'status',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'finish_date' => 'date',
        'current_finish_date' => 'date',
        'qty_total_target' => 'integer',
        'qty_completed' => 'integer',
        'days_extended' => 'integer',
    ];

    /**
     * Get the options for activity logging.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['order_id', 'line_id', 'start_date', 'finish_date', 'current_finish_date', 'qty_total_target', 'qty_completed', 'days_extended', 'status'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn (string $eventName) => "Schedule for Order #{$this->order_id} on Line #{$this->line_id} (ID: {$this->id}) was {$eventName}");
    }

    /**
     * Relationship: Schedule belongs to Order
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Relationship: Schedule belongs to Line
     */
    public function line()
    {
        return $this->belongsTo(Line::class);
    }

    /**
     * Relationship: Schedule has many Daily Outputs
     */
    public function dailyOutputs()
    {
        return $this->hasMany(ScheduleDailyOutput::class);
    }

    /**
     * Get remaining quantity to complete
     */
    public function getRemainingQtyAttribute()
    {
        return $this->qty_total_target - $this->qty_completed;
    }

    /**
     * Get completion percentage
     */
    public function getCompletionPercentageAttribute()
    {
        if ($this->qty_total_target == 0) {
            return 0;
        }

        return round(($this->qty_completed / $this->qty_total_target) * 100, 2);
    }

    /**
     * Get total working days (original)
     */
    public function getTotalDaysAttribute()
    {
        return $this->start_date->diffInDays($this->finish_date) + 1;
    }

    /**
     * Get current total working days (after extension)
     */
    public function getCurrentTotalDaysAttribute()
    {
        return $this->start_date->diffInDays($this->current_finish_date) + 1;
    }

    /**
     * Get base target output per day (without remainder)
     */
    public function getBaseTargetPerDayAttribute()
    {
        return (int) floor($this->qty_total_target / $this->total_days);
    }

    /**
     * Get remainder for last day
     */
    public function getRemainderAttribute()
    {
        return $this->qty_total_target % $this->total_days;
    }

    /**
     * Check if schedule is delayed
     */
    public function isDelayed()
    {
        return $this->current_finish_date->gt($this->finish_date);
    }

    /**
     * Check if schedule is completed
     */
    public function isCompleted()
    {
        return $this->qty_completed >= $this->qty_total_target;
    }

    /**
     * Scope: Active schedules
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['pending', 'in_progress']);
    }

    /**
     * Scope: Delayed schedules
     */
    public function scopeDelayed($query)
    {
        return $query->whereColumn('current_finish_date', '>', 'finish_date');
    }

    /**
     * Scope: Schedules for specific line
     */
    public function scopeForLine($query, $lineId)
    {
        return $query->where('line_id', $lineId);
    }

    /**
     * Scope: Schedules in date range
     */
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->where(function ($q) use ($startDate, $endDate) {
            $q->whereBetween('start_date', [$startDate, $endDate])
                ->orWhereBetween('current_finish_date', [$startDate, $endDate])
                ->orWhere(function ($q2) use ($startDate, $endDate) {
                    $q2->where('start_date', '<=', $startDate)
                        ->where('current_finish_date', '>=', $endDate);
                });
        });
    }

    /**
     * Get schedules that will be affected if this schedule extends
     * Uses finish_date (original planned date) to find ALL schedules that need shifting
     * This ensures cascading works correctly even for multiple sequential extensions
     */
    public function getAffectedSchedules()
    {
        return self::where('line_id', $this->line_id)
            ->where('id', '!=', $this->id)
            ->where('start_date', '>', $this->finish_date)
            ->orderBy('start_date')
            ->get();
    }
}
