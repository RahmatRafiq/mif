<?php

namespace App\Services;

use App\Repositories\Contracts\LineRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class LineService
{
    public function __construct(
        private LineRepositoryInterface $lineRepository
    ) {}

    public function getAllLines(): Collection
    {
        return $this->lineRepository->all();
    }

    public function getActiveLines(): Collection
    {
        return $this->lineRepository->getActive();
    }

    public function getLine(int $id)
    {
        return $this->lineRepository->find($id);
    }

    public function createLine(array $data)
    {
        return $this->lineRepository->create($data);
    }

    public function updateLine(int $id, array $data)
    {
        return $this->lineRepository->update($id, $data);
    }

    public function deleteLine(int $id): bool
    {
        return $this->lineRepository->delete($id);
    }

    public function checkLineAvailability(int $lineId, string $startDate, string $endDate, ?int $excludeScheduleId = null): bool
    {
        return $this->lineRepository->checkAvailability($lineId, $startDate, $endDate, $excludeScheduleId);
    }
}
