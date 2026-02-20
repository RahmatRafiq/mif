<?php

namespace Database\Seeders;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $orders = [
            [
                'order_number' => 'PO-2026-001',
                'product_name' => 'T-Shirt Basic White',
                'product_code' => 'TSH-WHT-001',
                'qty_total' => 1000,
                'customer' => 'PT Textindo Indonesia',
                'order_date' => Carbon::now()->subDays(10),
                'due_date' => Carbon::now()->addDays(20),
                'status' => 'pending',
                'notes' => 'Standard quality, cotton 100%',
            ],
            [
                'order_number' => 'PO-2026-002',
                'product_name' => 'Polo Shirt Navy Blue',
                'product_code' => 'PLO-NVY-002',
                'qty_total' => 750,
                'customer' => 'CV Garmen Jaya',
                'order_date' => Carbon::now()->subDays(8),
                'due_date' => Carbon::now()->addDays(25),
                'status' => 'pending',
                'notes' => 'Premium quality with collar',
            ],
            [
                'order_number' => 'PO-2026-003',
                'product_name' => 'Jacket Denim',
                'product_code' => 'JCK-DNM-003',
                'qty_total' => 500,
                'customer' => 'PT Fashion Retail',
                'order_date' => Carbon::now()->subDays(5),
                'due_date' => Carbon::now()->addDays(30),
                'status' => 'pending',
                'notes' => 'Heavy duty denim material',
            ],
            [
                'order_number' => 'PO-2026-004',
                'product_name' => 'Shirt Formal Black',
                'product_code' => 'SHR-BLK-004',
                'qty_total' => 1200,
                'customer' => 'PT Corporate Apparel',
                'order_date' => Carbon::now()->subDays(3),
                'due_date' => Carbon::now()->addDays(35),
                'status' => 'pending',
                'notes' => 'Office uniform collection',
            ],
            [
                'order_number' => 'PO-2026-005',
                'product_name' => 'Hoodie Grey',
                'product_code' => 'HOD-GRY-005',
                'qty_total' => 600,
                'customer' => 'CV Street Wear',
                'order_date' => Carbon::now()->subDays(2),
                'due_date' => Carbon::now()->addDays(40),
                'status' => 'pending',
                'notes' => 'Winter collection, fleece material',
            ],
        ];

        foreach ($orders as $order) {
            Order::create($order);
        }
    }
}
