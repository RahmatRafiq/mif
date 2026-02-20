<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity as LogsActivityTrait;

class ScheduleDailyOutput extends Model
{
    use HasFactory, LogsActivityTrait;

    protected $fillable = [
        'schedule_id',
        'date',
        'target_output',
        'actual_output',
        'balance',
        'is_completed',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'target_output' => 'integer',
        'actual_output' => 'integer',
        'balance' => 'integer',
        'is_completed' => 'boolean',
    ];

    protected static $logAttributes = ['date', 'target_output', 'actual_output', 'balance'];

    protected static $logName = 'daily_output';

    /**
     * Relationship: Daily Output belongs to Schedule
     */
    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    /**
     * Get achievement percentage
     */
    public function getAchievementPercentageAttribute()
    {
        if ($this->target_output == 0) {
            return 0;
        }

        return round(($this->actual_output / $this->target_output) * 100, 2);
    }

    /**
     * Check if output meets target
     */
    public function meetsTarget()
    {
        return $this->actual_output >= $this->target_output;
    }

    /**
     * Check if output is below target
     */
    public function isBelowTarget()
    {
        return $this->actual_output < $this->target_output;
    }

    /**
     * Check if output exceeds target
     */
    public function exceedsTarget()
    {
        return $this->actual_output > $this->target_output;
    }

    /**
     * Scope: Outputs with balance (not meeting target)
     */
    public function scopeWithBalance($query)
    {
        return $query->where('balance', '>', 0);
    }

    /**
     * Scope: Completed outputs
     */
    public function scopeCompleted($query)
    {
        return $query->where('is_completed', true);
    }

    /**
     * Scope: Pending outputs (not yet input)
     */
    public function scopePending($query)
    {
        return $query->where('actual_output', 0)
            ->where('is_completed', false);
    }

    /**
     * Scope: Outputs for specific date
     */
    public function scopeForDate($query, $date)
    {
        return $query->whereDate('date', $date);
    }
}
