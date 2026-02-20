<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface LineRepositoryInterface extends BaseRepositoryInterface
{
    public function getActive(): Collection;

    public function findByCode(string $code);

    public function checkAvailability(int $lineId, string $startDate, string $endDate, ?int $excludeScheduleId = null): bool;
}
