<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface ScheduleRepositoryInterface extends BaseRepositoryInterface
{
    public function getActive(): Collection;

    public function getDelayed(): Collection;

    public function getByLine(int $lineId): Collection;

    public function getByOrder(int $orderId): Collection;

    public function getInDateRange(string $startDate, string $endDate): Collection;

    public function getAffectedSchedules(int $scheduleId): Collection;

    public function updateStatus(int $scheduleId, string $status): bool;

    public function extendSchedule(int $scheduleId, int $days): bool;
}
