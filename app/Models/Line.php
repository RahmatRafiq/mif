<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogsActivity;
use Spatie\Activitylog\Traits\LogsActivity as LogsActivityTrait;

class Line extends Model
{
    use HasFactory, LogsActivityTrait, SoftDeletes;

    protected $table = 'master_lines';

    protected $fillable = [
        'name',
        'code',
        'description',
        'capacity_per_day',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'capacity_per_day' => 'integer',
    ];

    protected static $logAttributes = ['name', 'code', 'is_active'];

    protected static $logName = 'line';

    /**
     * Relationship: Line has many Schedules
     */
    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'line_id');
    }

    /**
     * Get active schedules for this line
     */
    public function activeSchedules()
    {
        return $this->hasMany(Schedule::class, 'line_id')
            ->whereIn('status', ['pending', 'in_progress']);
    }

    /**
     * Scope: Only active lines
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Check if line is available for scheduling in date range
     */
    public function isAvailableInRange($startDate, $endDate, $excludeScheduleId = null)
    {
        $query = $this->schedules()
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('current_finish_date', [$startDate, $endDate])
                    ->orWhere(function ($q2) use ($startDate, $endDate) {
                        $q2->where('start_date', '<=', $startDate)
                            ->where('current_finish_date', '>=', $endDate);
                    });
            });

        if ($excludeScheduleId) {
            $query->where('id', '!=', $excludeScheduleId);
        }

        return $query->count() === 0;
    }
}
