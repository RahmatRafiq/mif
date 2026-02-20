<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface ScheduleDailyOutputRepositoryInterface extends BaseRepositoryInterface
{
    public function getBySchedule(int $scheduleId): Collection;

    public function getByDate(string $date): Collection;

    public function getPending(): Collection;

    public function getWithBalance(): Collection;

    public function updateActualOutput(int $dailyOutputId, int $actualOutput): bool;

    public function bulkCreate(array $dailyOutputs): bool;
}
