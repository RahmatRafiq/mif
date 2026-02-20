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
            [
                'order_number' => 'PO-2026-006',
                'product_name' => 'T-Shirt V-Neck Red',
                'product_code' => 'TSH-RED-006',
                'qty_total' => 1500,
                'customer' => 'PT Retail Express',
                'order_date' => Carbon::now()->subDays(15),
                'due_date' => Carbon::now()->addDays(15),
                'status' => 'pending',
                'notes' => 'Promotional campaign batch',
            ],
            [
                'order_number' => 'PO-2026-007',
                'product_name' => 'Pants Chino Khaki',
                'product_code' => 'PNT-KHK-007',
                'qty_total' => 1200,
                'customer' => 'PT Fashion Retail',
                'order_date' => Carbon::now()->subDays(12),
                'due_date' => Carbon::now()->addDays(18),
                'status' => 'pending',
                'notes' => 'Casual wear collection',
            ],
            [
                'order_number' => 'PO-2026-008',
                'product_name' => 'Dress Summer Floral',
                'product_code' => 'DRS-FLR-008',
                'qty_total' => 800,
                'customer' => 'CV Garmen Jaya',
                'order_date' => Carbon::now()->subDays(7),
                'due_date' => Carbon::now()->addDays(28),
                'status' => 'pending',
                'notes' => 'Spring/summer collection',
            ],
            [
                'order_number' => 'PO-2026-009',
                'product_name' => 'Sweater Cardigan Blue',
                'product_code' => 'SWT-BLU-009',
                'qty_total' => 1000,
                'customer' => 'PT Corporate Apparel',
                'order_date' => Carbon::now()->subDays(4),
                'due_date' => Carbon::now()->addDays(32),
                'status' => 'pending',
                'notes' => 'Business casual line',
            ],
            [
                'order_number' => 'PO-2026-010',
                'product_name' => 'Shorts Sports Black',
                'product_code' => 'SHT-BLK-010',
                'qty_total' => 1800,
                'customer' => 'CV Street Wear',
                'order_date' => Carbon::now()->subDays(1),
                'due_date' => Carbon::now()->addDays(45),
                'status' => 'pending',
                'notes' => 'Athletic wear collection',
            ],
            [
                'order_number' => 'PO-2026-011',
                'product_name' => 'Blazer Formal Navy',
                'product_code' => 'BLZ-NVY-011',
                'qty_total' => 600,
                'customer' => 'PT Textindo Indonesia',
                'order_date' => Carbon::now(),
                'due_date' => Carbon::now()->addDays(50),
                'status' => 'pending',
                'notes' => 'Executive collection',
            ],
            [
                'order_number' => 'PO-2026-012',
                'product_name' => 'Tank Top White',
                'product_code' => 'TNK-WHT-012',
                'qty_total' => 2000,
                'customer' => 'PT Retail Express',
                'order_date' => Carbon::now()->addDays(2),
                'due_date' => Carbon::now()->addDays(60),
                'status' => 'pending',
                'notes' => 'Summer basics collection',
            ],
        ];

        foreach ($orders as $order) {
            Order::create($order);
        }
    }
}
