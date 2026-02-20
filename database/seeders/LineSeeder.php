<?php

namespace Database\Seeders;

use App\Models\Line;
use Illuminate\Database\Seeder;

class LineSeeder extends Seeder
{
    public function run(): void
    {
        $lines = [
            [
                'name' => 'Line A',
                'code' => 'L001',
                'description' => 'Primary sewing line for standard products',
                'capacity_per_day' => 500,
                'is_active' => true,
            ],
            [
                'name' => 'Line B',
                'code' => 'L002',
                'description' => 'Secondary sewing line for standard products',
                'capacity_per_day' => 450,
                'is_active' => true,
            ],
            [
                'name' => 'Line C',
                'code' => 'L003',
                'description' => 'Specialized line for premium products',
                'capacity_per_day' => 300,
                'is_active' => true,
            ],
            [
                'name' => 'Line D',
                'code' => 'L004',
                'description' => 'High-capacity line for bulk orders',
                'capacity_per_day' => 600,
                'is_active' => true,
            ],
            [
                'name' => 'Line E',
                'code' => 'L005',
                'description' => 'Backup line for overflow production',
                'capacity_per_day' => 400,
                'is_active' => true,
            ],
        ];

        foreach ($lines as $line) {
            Line::create($line);
        }
    }
}
