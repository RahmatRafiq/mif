<?php

namespace App\Services;

use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class OrderService
{
    public function __construct(
        private OrderRepositoryInterface $orderRepository
    ) {}

    public function getAllOrders(): Collection
    {
        return $this->orderRepository->all();
    }

    public function getSchedulableOrders(): Collection
    {
        return $this->orderRepository->getSchedulable();
    }

    public function getPendingOrders(): Collection
    {
        return $this->orderRepository->getPending();
    }

    public function getOrder(int $id)
    {
        return $this->orderRepository->find($id);
    }

    public function createOrder(array $data)
    {
        return $this->orderRepository->create($data);
    }

    public function updateOrder(int $id, array $data)
    {
        return $this->orderRepository->update($id, $data);
    }

    public function deleteOrder(int $id): bool
    {
        return $this->orderRepository->delete($id);
    }

    public function updateOrderStatus(int $orderId, string $status): bool
    {
        return $this->orderRepository->updateStatus($orderId, $status);
    }
}
