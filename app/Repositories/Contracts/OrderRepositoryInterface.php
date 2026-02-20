<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface OrderRepositoryInterface extends BaseRepositoryInterface
{
    public function getSchedulable(): Collection;

    public function getPending(): Collection;

    public function findByOrderNumber(string $orderNumber);

    public function updateStatus(int $orderId, string $status): bool;
}
