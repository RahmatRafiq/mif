<?php

namespace App\Repositories\Eloquent;

use App\Models\ScheduleDailyOutput;
use App\Repositories\Contracts\ScheduleDailyOutputRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ScheduleDailyOutputRepository extends BaseRepository implements ScheduleDailyOutputRepositoryInterface
{
    public function __construct(ScheduleDailyOutput $model)
    {
        parent::__construct($model);
    }

    public function getBySchedule(int $scheduleId): Collection
    {
        return $this->model->where('schedule_id', $scheduleId)
            ->orderBy('date')
            ->get();
    }

    public function getByDate(string $date): Collection
    {
        return $this->model->forDate($date)
            ->with(['schedule.order', 'schedule.line'])
            ->get();
    }

    public function getPending(): Collection
    {
        return $this->model->pending()
            ->with(['schedule.order', 'schedule.line'])
            ->orderBy('date')
            ->get();
    }

    public function getWithBalance(): Collection
    {
        return $this->model->withBalance()
            ->with(['schedule.order', 'schedule.line'])
            ->orderBy('date')
            ->get();
    }

    public function updateActualOutput(int $dailyOutputId, int $actualOutput): bool
    {
        $dailyOutput = $this->find($dailyOutputId);
        if (! $dailyOutput) {
            return false;
        }

        $balance = $dailyOutput->target_output - $actualOutput;

        return $this->update($dailyOutputId, [
            'actual_output' => $actualOutput,
            'balance' => $balance,
            'is_completed' => $actualOutput >= $dailyOutput->target_output,
        ]);
    }

    public function bulkCreate(array $dailyOutputs): bool
    {
        try {
            $this->model->insert($dailyOutputs);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
