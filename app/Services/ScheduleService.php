<?php

namespace App\Services;

use App\Events\ScheduleUpdated;
use App\Models\Schedule;
use App\Models\ScheduleDailyOutput;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Repositories\Contracts\ScheduleDailyOutputRepositoryInterface;
use App\Repositories\Contracts\ScheduleRepositoryInterface;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ScheduleService
{
    public function __construct(
        private ScheduleRepositoryInterface $scheduleRepository,
        private ScheduleDailyOutputRepositoryInterface $dailyOutputRepository,
        private OrderRepositoryInterface $orderRepository
    ) {}

    /**
     * Get all schedules with relationships
     */
    public function getAllSchedules(): Collection
    {
        // Get all schedules (not just active) for Kanban board
        return $this->scheduleRepository->getAllWithRelationships();
    }

    /**
     * Get schedule by ID
     */
    public function getSchedule(int $id)
    {
        return $this->scheduleRepository->find($id);
    }

    /**
     * Get schedules by line
     */
    public function getSchedulesByLine(int $lineId): Collection
    {
        return $this->scheduleRepository->getByLine($lineId);
    }

    /**
     * Get schedules by order
     */
    public function getSchedulesByOrder(int $orderId): Collection
    {
        return $this->scheduleRepository->getByOrder($orderId);
    }

    /**
     * Get schedules in date range
     */
    public function getSchedulesInDateRange(string $startDate, string $endDate): Collection
    {
        return $this->scheduleRepository->getInDateRange($startDate, $endDate);
    }

    /**
     * Create new schedule with daily targets
     */
    public function createSchedule(array $data): Schedule
    {
        DB::beginTransaction();
        try {
            // Create schedule
            $schedule = $this->scheduleRepository->create([
                'order_id' => $data['order_id'],
                'line_id' => $data['line_id'],
                'start_date' => $data['start_date'],
                'finish_date' => $data['finish_date'],
                'current_finish_date' => $data['finish_date'], // Initially same as finish_date
                'qty_total_target' => $data['qty_total_target'],
                'qty_completed' => 0,
                'days_extended' => 0,
                'status' => 'pending',
                'notes' => $data['notes'] ?? null,
            ]);

            // Generate daily targets
            $this->generateDailyTargets($schedule);

            // Update order status
            $this->orderRepository->updateStatus($data['order_id'], 'scheduled');

            DB::commit();

            return $schedule->load(['order', 'line', 'dailyOutputs']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Generate daily target outputs for schedule
     * Distributes qty_total_target across date range
     * Remainder goes to last day
     */
    private function generateDailyTargets(Schedule $schedule): void
    {
        $startDate = Carbon::parse($schedule->start_date);
        $finishDate = Carbon::parse($schedule->finish_date);
        $totalDays = $startDate->diffInDays($finishDate) + 1;

        // Calculate base target per day and remainder
        $baseTarget = (int) floor($schedule->qty_total_target / $totalDays);
        $remainder = $schedule->qty_total_target % $totalDays;

        // Generate date period
        $period = CarbonPeriod::create($startDate, $finishDate);
        $dailyOutputs = [];
        $dayIndex = 0;

        foreach ($period as $date) {
            $isLastDay = $dayIndex === ($totalDays - 1);

            // Last day gets base + remainder
            $targetOutput = $isLastDay ? ($baseTarget + $remainder) : $baseTarget;

            $dailyOutputs[] = [
                'schedule_id' => $schedule->id,
                'date' => $date->format('Y-m-d'),
                'target_output' => $targetOutput,
                'actual_output' => 0,
                'balance' => 0, // Will be calculated when actual is input
                'is_completed' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $dayIndex++;
        }

        // Bulk insert daily outputs
        $this->dailyOutputRepository->bulkCreate($dailyOutputs);
    }

    /**
     * Input actual output for a specific day
     * Triggers balancing if actual < target
     */
    public function inputActualOutput(int $dailyOutputId, int $actualOutput): bool
    {
        DB::beginTransaction();
        try {
            $dailyOutput = $this->dailyOutputRepository->find($dailyOutputId);
            if (! $dailyOutput) {
                throw new \Exception('Daily output not found');
            }

            // Prevent re-input on completed days (optional: allow re-input by removing this check)
            if ($dailyOutput->is_completed && $dailyOutput->actual_output > 0) {
                throw new \Exception('This daily output has already been completed. Cannot modify.');
            }

            $schedule = $dailyOutput->schedule;

            // Update actual output and calculate balance
            $balance = $dailyOutput->target_output - $actualOutput;

            $this->dailyOutputRepository->update($dailyOutputId, [
                'actual_output' => $actualOutput,
                'balance' => $balance,
                'is_completed' => $actualOutput >= $dailyOutput->target_output,
            ]);

            // Update schedule qty_completed
            $totalCompleted = $schedule->dailyOutputs()->sum('actual_output');
            $this->scheduleRepository->update($schedule->id, [
                'qty_completed' => $totalCompleted,
            ]);

            // If balance exists (actual < target), trigger balancing
            if ($balance > 0) {
                $this->performBalancing($dailyOutput, $balance);
            }

            // Check if schedule is completed
            // Note: $totalCompleted already includes $actualOutput (updated above)
            if ($totalCompleted >= $schedule->qty_total_target) {
                $this->scheduleRepository->updateStatus($schedule->id, 'completed');
                $this->orderRepository->updateStatus($schedule->order_id, 'completed');
            } else {
                $this->scheduleRepository->updateStatus($schedule->id, 'in_progress');
                $this->orderRepository->updateStatus($schedule->order_id, 'in_progress');
            }

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Perform balancing when actual < target
     * Add balance to next day or extend schedule
     */
    private function performBalancing(ScheduleDailyOutput $dailyOutput, int $balance): void
    {
        $schedule = $dailyOutput->schedule;
        $currentDate = Carbon::parse($dailyOutput->date);

        // Find next day in schedule
        $nextDayOutput = $schedule->dailyOutputs()
            ->where('date', '>', $currentDate)
            ->orderBy('date')
            ->first();

        if ($nextDayOutput) {
            // Add balance to next day's target
            $newTarget = $nextDayOutput->target_output + $balance;
            $this->dailyOutputRepository->update($nextDayOutput->id, [
                'target_output' => $newTarget,
            ]);
        } else {
            // No next day exists, need to extend schedule
            $this->extendSchedule($schedule, $balance);
        }
    }

    /**
     * Extend schedule by adding new days
     * Cascades to shift subsequent schedules
     */
    private function extendSchedule(Schedule $schedule, int $remainingQty): void
    {
        // Calculate how many days needed to complete remaining qty
        $avgDailyOutput = $schedule->base_target_per_day;
        if ($avgDailyOutput == 0) {
            $avgDailyOutput = 100; // Default fallback
        }

        $daysNeeded = (int) ceil($remainingQty / $avgDailyOutput);

        // Update schedule finish date
        $newFinishDate = Carbon::parse($schedule->current_finish_date)->addDays($daysNeeded);

        $this->scheduleRepository->update($schedule->id, [
            'current_finish_date' => $newFinishDate,
            'days_extended' => $schedule->days_extended + $daysNeeded,
            'status' => 'delayed',
        ]);

        // Generate new daily outputs for extended days
        $extendedPeriod = CarbonPeriod::create(
            Carbon::parse($schedule->current_finish_date)->addDay(),
            $newFinishDate
        );

        $newDailyOutputs = [];
        foreach ($extendedPeriod as $date) {
            $newDailyOutputs[] = [
                'schedule_id' => $schedule->id,
                'date' => $date->format('Y-m-d'),
                'target_output' => $avgDailyOutput,
                'actual_output' => 0,
                'balance' => 0,
                'is_completed' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (! empty($newDailyOutputs)) {
            $this->dailyOutputRepository->bulkCreate($newDailyOutputs);
        }

        // Cascade: Shift subsequent schedules on same line
        $this->shiftSubsequentSchedules($schedule, $daysNeeded);
    }

    /**
     * Shift all subsequent schedules on the same line
     * When a schedule extends, push following schedules forward
     */
    private function shiftSubsequentSchedules(Schedule $schedule, int $daysToShift): void
    {
        $affectedSchedules = $this->scheduleRepository->getAffectedSchedules($schedule->id);

        foreach ($affectedSchedules as $affectedSchedule) {
            $newStartDate = Carbon::parse($affectedSchedule->start_date)->addDays($daysToShift);
            $newFinishDate = Carbon::parse($affectedSchedule->finish_date)->addDays($daysToShift);
            $newCurrentFinishDate = Carbon::parse($affectedSchedule->current_finish_date)->addDays($daysToShift);

            $this->scheduleRepository->update($affectedSchedule->id, [
                'start_date' => $newStartDate,
                'finish_date' => $newFinishDate,
                'current_finish_date' => $newCurrentFinishDate,
            ]);

            // Update daily outputs dates
            foreach ($affectedSchedule->dailyOutputs as $dailyOutput) {
                $newDate = Carbon::parse($dailyOutput->date)->addDays($daysToShift);
                $this->dailyOutputRepository->update($dailyOutput->id, [
                    'date' => $newDate,
                ]);
            }
        }
    }

    /**
     * Update schedule
     */
    public function updateSchedule(int $id, array $data): Schedule
    {
        DB::beginTransaction();
        try {
            $schedule = $this->scheduleRepository->find($id);

            // If dates changed, regenerate daily targets
            if (isset($data['start_date']) || isset($data['finish_date']) || isset($data['qty_total_target'])) {
                // Delete existing daily outputs
                $schedule->dailyOutputs()->delete();

                // Update schedule
                $this->scheduleRepository->update($id, $data);

                // Regenerate daily targets
                $updatedSchedule = $this->scheduleRepository->find($id);
                $this->generateDailyTargets($updatedSchedule);
            } else {
                // Simple update
                $this->scheduleRepository->update($id, $data);
            }

            DB::commit();

            $updatedSchedule = $this->scheduleRepository->find($id)->load(['order', 'line', 'dailyOutputs']);

            // Broadcast schedule update event
            broadcast(new ScheduleUpdated($updatedSchedule))->toOthers();

            return $updatedSchedule;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Delete schedule
     */
    public function deleteSchedule(int $id): bool
    {
        return $this->scheduleRepository->delete($id);
    }

    /**
     * Get daily outputs for a schedule
     */
    public function getDailyOutputs(int $scheduleId): Collection
    {
        return $this->dailyOutputRepository->getBySchedule($scheduleId);
    }
}
