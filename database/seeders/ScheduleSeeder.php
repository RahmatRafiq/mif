<?php

namespace Database\Seeders;

use App\Models\Line;
use App\Models\Order;
use App\Models\Schedule;
use App\Models\ScheduleDailyOutput;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Seeder;

class ScheduleSeeder extends Seeder
{
    public function run(): void
    {
        // Get all orders and lines
        $orders = Order::all();
        $lines = Line::where('is_active', true)->get();

        if ($orders->isEmpty() || $lines->isEmpty()) {
            $this->command->warn('No orders or lines found. Run OrderSeeder and LineSeeder first.');

            return;
        }

        $schedules = [];
        $currentDate = Carbon::now()->startOfDay();

        // Schedule 1: COMPLETED - Line A, finished last week
        $order1 = $orders->first();
        $line1 = $lines->first();
        $schedules[] = $this->createSchedule(
            order: $order1,
            line: $line1,
            startDate: $currentDate->copy()->subDays(14),
            totalDays: 7,
            qtyTarget: 1000,
            status: 'completed',
            completionRate: 1.0, // 100% completed
            hasDelay: false
        );

        // Schedule 2: COMPLETED - Line B, finished 3 days ago
        if ($orders->count() > 1 && $lines->count() > 1) {
            $schedules[] = $this->createSchedule(
                order: $orders->get(1),
                line: $lines->get(1),
                startDate: $currentDate->copy()->subDays(10),
                totalDays: 5,
                qtyTarget: 800,
                status: 'completed',
                completionRate: 1.0,
                hasDelay: false
            );
        }

        // Schedule 3: IN PROGRESS - Line A, started 3 days ago, 60% done
        if ($orders->count() > 2) {
            $schedules[] = $this->createSchedule(
                order: $orders->get(2),
                line: $line1,
                startDate: $currentDate->copy()->subDays(3),
                totalDays: 7,
                qtyTarget: 1400,
                status: 'in_progress',
                completionRate: 0.6, // 60% completed
                hasDelay: false
            );
        }

        // Schedule 4: IN PROGRESS - Line C, started yesterday, 20% done
        if ($orders->count() > 3 && $lines->count() > 2) {
            $schedules[] = $this->createSchedule(
                order: $orders->get(3),
                line: $lines->get(2),
                startDate: $currentDate->copy()->subDay(),
                totalDays: 5,
                qtyTarget: 1000,
                status: 'in_progress',
                completionRate: 0.2,
                hasDelay: false
            );
        }

        // Schedule 5: DELAYED - Line B, started 5 days ago, behind schedule
        if ($orders->count() > 4 && $lines->count() > 1) {
            $schedules[] = $this->createSchedule(
                order: $orders->get(4),
                line: $lines->get(1),
                startDate: $currentDate->copy()->subDays(5),
                totalDays: 5,
                qtyTarget: 1200,
                status: 'delayed',
                completionRate: 0.5, // Only 50% done, should be 100%
                hasDelay: true,
                daysExtended: 3
            );
        }

        // Schedule 6: DELAYED - Line D, major delay
        if ($orders->count() > 5 && $lines->count() > 3) {
            $schedules[] = $this->createSchedule(
                order: $orders->get(5),
                line: $lines->get(3),
                startDate: $currentDate->copy()->subDays(8),
                totalDays: 6,
                qtyTarget: 1500,
                status: 'delayed',
                completionRate: 0.65,
                hasDelay: true,
                daysExtended: 4
            );
        }

        // Schedule 7: PENDING - Line A, starts tomorrow
        if ($orders->count() > 6) {
            $schedules[] = $this->createSchedule(
                order: $orders->get(6),
                line: $line1,
                startDate: $currentDate->copy()->addDay(),
                totalDays: 6,
                qtyTarget: 1200,
                status: 'pending',
                completionRate: 0,
                hasDelay: false
            );
        }

        // Schedule 8: PENDING - Line C, starts in 3 days
        if ($orders->count() > 7 && $lines->count() > 2) {
            $schedules[] = $this->createSchedule(
                order: $orders->get(7),
                line: $lines->get(2),
                startDate: $currentDate->copy()->addDays(3),
                totalDays: 7,
                qtyTarget: 1600,
                status: 'pending',
                completionRate: 0,
                hasDelay: false
            );
        }

        // Schedule 9: PENDING - Line D, starts next week
        if ($orders->count() > 8 && $lines->count() > 3) {
            $schedules[] = $this->createSchedule(
                order: $orders->get(8),
                line: $lines->get(3),
                startDate: $currentDate->copy()->addDays(7),
                totalDays: 5,
                qtyTarget: 1000,
                status: 'pending',
                completionRate: 0,
                hasDelay: false
            );
        }

        // Schedule 10: IN PROGRESS - Line E, just started today
        if ($orders->count() > 9 && $lines->count() > 4) {
            $schedules[] = $this->createSchedule(
                order: $orders->get(9),
                line: $lines->get(4),
                startDate: $currentDate->copy(),
                totalDays: 8,
                qtyTarget: 1800,
                status: 'in_progress',
                completionRate: 0.1,
                hasDelay: false
            );
        }

        $this->command->info('✅ Created '.count($schedules).' production schedules with daily outputs');
    }

    /**
     * Create a schedule with realistic daily outputs
     */
    private function createSchedule(
        Order $order,
        Line $line,
        Carbon $startDate,
        int $totalDays,
        int $qtyTarget,
        string $status,
        float $completionRate,
        bool $hasDelay,
        int $daysExtended = 0
    ): Schedule {
        $finishDate = $startDate->copy()->addDays($totalDays - 1);
        $currentFinishDate = $hasDelay ? $finishDate->copy()->addDays($daysExtended) : $finishDate->copy();
        $qtyCompleted = (int) ($qtyTarget * $completionRate);

        // Create schedule
        $schedule = Schedule::create([
            'order_id' => $order->id,
            'line_id' => $line->id,
            'start_date' => $startDate,
            'finish_date' => $finishDate,
            'current_finish_date' => $currentFinishDate,
            'qty_total_target' => $qtyTarget,
            'qty_completed' => $qtyCompleted,
            'days_extended' => $daysExtended,
            'status' => $status,
            'notes' => $this->getScheduleNotes($status, $hasDelay),
        ]);

        // Generate daily outputs
        $this->generateDailyOutputs($schedule, $startDate, $currentFinishDate, $qtyTarget, $qtyCompleted, $status, $hasDelay);

        // Update order status based on schedule
        if ($status === 'completed') {
            $order->update(['status' => 'completed']);
        } elseif ($status === 'in_progress' || $status === 'delayed') {
            $order->update(['status' => 'in_progress']);
        } else {
            $order->update(['status' => 'scheduled']);
        }

        return $schedule;
    }

    /**
     * Generate realistic daily outputs for a schedule
     */
    private function generateDailyOutputs(
        Schedule $schedule,
        Carbon $startDate,
        Carbon $currentFinishDate,
        int $qtyTarget,
        int $qtyCompleted,
        string $status,
        bool $hasDelay
    ): void {
        $totalDays = $startDate->diffInDays($currentFinishDate) + 1;
        $baseTarget = (int) floor($qtyTarget / $totalDays);
        $remainder = $qtyTarget % $totalDays;

        $period = CarbonPeriod::create($startDate, $currentFinishDate);
        $dayIndex = 0;
        $cumulativeActual = 0;
        $today = Carbon::now()->startOfDay();

        foreach ($period as $date) {
            $isLastDay = $dayIndex === ($totalDays - 1);
            $targetOutput = $isLastDay ? ($baseTarget + $remainder) : $baseTarget;

            // Calculate actual output based on schedule status and date
            $actualOutput = $this->calculateActualOutput(
                $date,
                $today,
                $targetOutput,
                $cumulativeActual,
                $qtyCompleted,
                $qtyTarget,
                $status,
                $hasDelay,
                $dayIndex,
                $totalDays
            );

            $cumulativeActual += $actualOutput;
            $balance = $targetOutput - $actualOutput;

            ScheduleDailyOutput::create([
                'schedule_id' => $schedule->id,
                'date' => $date->format('Y-m-d'),
                'target_output' => $targetOutput,
                'actual_output' => $actualOutput,
                'balance' => $balance,
                'is_completed' => $actualOutput >= $targetOutput,
                'notes' => $this->getDailyOutputNotes($actualOutput, $targetOutput, $hasDelay, $date, $today),
            ]);

            $dayIndex++;
        }
    }

    /**
     * Calculate realistic actual output for a day
     */
    private function calculateActualOutput(
        Carbon $date,
        Carbon $today,
        int $targetOutput,
        int $cumulativeActual,
        int $totalCompleted,
        int $totalTarget,
        string $status,
        bool $hasDelay,
        int $dayIndex,
        int $totalDays
    ): int {
        // Future dates have no actual output
        if ($date->gt($today)) {
            return 0;
        }

        // For pending schedules, no output yet
        if ($status === 'pending') {
            return 0;
        }

        // For completed schedules, distribute completed qty across days
        if ($status === 'completed') {
            $remainingQty = $totalCompleted - $cumulativeActual;
            $remainingDays = $totalDays - $dayIndex;

            if ($remainingDays <= 1) {
                return $remainingQty; // Last day gets all remaining
            }

            // Normal completion with slight variations (95-105%)
            return (int) ($targetOutput * (0.95 + (rand(0, 10) / 100)));
        }

        // For in-progress schedules
        if ($status === 'in_progress') {
            // Past days: good performance (90-110%)
            return (int) ($targetOutput * (0.90 + (rand(0, 20) / 100)));
        }

        // For delayed schedules
        if ($status === 'delayed' && $hasDelay) {
            // Poor performance in past days (60-85%)
            return (int) ($targetOutput * (0.60 + (rand(0, 25) / 100)));
        }

        return 0;
    }

    /**
     * Get schedule notes based on status
     */
    private function getScheduleNotes(string $status, bool $hasDelay): ?string
    {
        return match ($status) {
            'completed' => 'Successfully completed on time',
            'in_progress' => 'Production running smoothly',
            'delayed' => $hasDelay ? 'Behind schedule due to machine maintenance and material shortage' : 'Minor delays expected',
            'pending' => 'Waiting for materials and line availability',
            default => null,
        };
    }

    /**
     * Get daily output notes
     */
    private function getDailyOutputNotes(int $actual, int $target, bool $hasDelay, Carbon $date, Carbon $today): ?string
    {
        if ($date->gt($today)) {
            return null; // No notes for future dates
        }

        if ($actual === 0) {
            return 'No production';
        }

        $achievement = ($target > 0) ? ($actual / $target * 100) : 0;

        if ($achievement >= 100) {
            return 'Target achieved';
        }

        if ($achievement >= 90) {
            return 'Good performance';
        }

        if ($achievement >= 70) {
            return 'Below target - need improvement';
        }

        return $hasDelay ? 'Significant delay - machine issues' : 'Low output - check line efficiency';
    }
}
