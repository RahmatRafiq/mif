<?php

namespace App\Repositories\Eloquent;

use App\Models\Schedule;
use App\Repositories\Contracts\ScheduleRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ScheduleRepository extends BaseRepository implements ScheduleRepositoryInterface
{
    public function __construct(Schedule $model)
    {
        parent::__construct($model);
    }

    public function getActive(): Collection
    {
        return $this->model->active()->with(['order', 'line'])->get();
    }

    public function getDelayed(): Collection
    {
        return $this->model->delayed()->with(['order', 'line'])->get();
    }

    public function getByLine(int $lineId): Collection
    {
        return $this->model->forLine($lineId)
            ->with(['order'])
            ->orderBy('start_date')
            ->get();
    }

    public function getByOrder(int $orderId): Collection
    {
        return $this->model->where('order_id', $orderId)
            ->with(['line'])
            ->orderBy('start_date')
            ->get();
    }

    public function getInDateRange(string $startDate, string $endDate): Collection
    {
        return $this->model->inDateRange($startDate, $endDate)
            ->with(['order', 'line'])
            ->orderBy('start_date')
            ->get();
    }

    public function getAffectedSchedules(int $scheduleId): Collection
    {
        $schedule = $this->find($scheduleId);
        if (! $schedule) {
            return collect([]);
        }

        return $schedule->getAffectedSchedules();
    }

    public function updateStatus(int $scheduleId, string $status): bool
    {
        return $this->update($scheduleId, ['status' => $status]);
    }

    public function extendSchedule(int $scheduleId, int $days): bool
    {
        $schedule = $this->find($scheduleId);
        if (! $schedule) {
            return false;
        }

        $newFinishDate = $schedule->current_finish_date->addDays($days);

        return $this->update($scheduleId, [
            'current_finish_date' => $newFinishDate,
            'days_extended' => $schedule->days_extended + $days,
            'status' => 'delayed',
        ]);
    }
}
