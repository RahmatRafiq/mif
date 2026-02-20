<?php

namespace App\Repositories\Eloquent;

use App\Models\Line;
use App\Repositories\Contracts\LineRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class LineRepository extends BaseRepository implements LineRepositoryInterface
{
    public function __construct(Line $model)
    {
        parent::__construct($model);
    }

    public function getActive(): Collection
    {
        return $this->model->active()->get();
    }

    public function findByCode(string $code)
    {
        return $this->model->where('code', $code)->first();
    }

    public function checkAvailability(int $lineId, string $startDate, string $endDate, ?int $excludeScheduleId = null): bool
    {
        $line = $this->find($lineId);
        if (! $line) {
            return false;
        }

        return $line->isAvailableInRange($startDate, $endDate, $excludeScheduleId);
    }
}
