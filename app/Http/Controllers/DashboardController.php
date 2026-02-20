<?php

namespace App\Http\Controllers;

use App\Models\Line;
use App\Models\Order;
use App\Models\Schedule;
use App\Models\ScheduleDailyOutput;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        // Production Statistics
        $totalOrders = Order::count();
        $pendingOrders = Order::where('status', 'pending')->count();
        $completedOrders = Order::where('status', 'completed')->count();
        $totalOrderQty = Order::sum('qty_total');

        // Schedule Statistics
        $totalSchedules = Schedule::count();
        $activeSchedules = Schedule::whereIn('status', ['pending', 'in_progress'])->count();
        $completedSchedules = Schedule::where('status', 'completed')->count();
        $delayedSchedules = Schedule::where('status', 'delayed')->count();

        // Line Statistics
        $totalLines = Line::count();
        $activeLines = Line::where('is_active', true)->count();
        $totalCapacity = Line::where('is_active', true)->sum('capacity_per_day');

        // Production Progress
        $totalTargetQty = Schedule::sum('qty_total_target');
        $totalCompletedQty = Schedule::sum('qty_completed');
        $completionPercentage = $totalTargetQty > 0 ? round(($totalCompletedQty / $totalTargetQty) * 100, 1) : 0;

        // Today's Production
        $today = Carbon::today();
        $todayOutputs = ScheduleDailyOutput::whereDate('date', $today)->get();
        $todayTarget = $todayOutputs->sum('target_output');
        $todayActual = $todayOutputs->sum('actual_output');
        $todayAchievement = $todayTarget > 0 ? round(($todayActual / $todayTarget) * 100, 1) : 0;

        // Recent Schedules
        $recentSchedules = Schedule::with(['order', 'line'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($schedule) {
                return [
                    'id' => $schedule->id,
                    'order_number' => $schedule->order->order_number ?? '-',
                    'product_name' => $schedule->order->product_name ?? '-',
                    'line_name' => $schedule->line->name ?? '-',
                    'start_date' => $schedule->start_date->format('Y-m-d'),
                    'finish_date' => $schedule->finish_date->format('Y-m-d'),
                    'current_finish_date' => $schedule->current_finish_date->format('Y-m-d'),
                    'status' => $schedule->status,
                    'completion_percentage' => $schedule->qty_total_target > 0
                        ? round(($schedule->qty_completed / $schedule->qty_total_target) * 100, 1)
                        : 0,
                    'days_extended' => $schedule->days_extended,
                ];
            });

        // Line Utilization (schedules per line)
        $lineUtilization = Line::withCount([
            'schedules' => function ($query) {
                $query->whereIn('status', ['pending', 'in_progress']);
            }
        ])
            ->where('is_active', true)
            ->get()
            ->map(function ($line) {
                return [
                    'name' => $line->name,
                    'code' => $line->code,
                    'capacity' => $line->capacity_per_day,
                    'active_schedules' => $line->schedules_count,
                    'utilization' => $line->schedules_count > 0 ? 'In Use' : 'Available',
                ];
            });

        // Weekly Production Trend (last 7 days)
        $weeklyTrend = collect();
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $outputs = ScheduleDailyOutput::whereDate('date', $date)->get();
            $weeklyTrend->push([
                'date' => $date->format('M d'),
                'target' => $outputs->sum('target_output'),
                'actual' => $outputs->sum('actual_output'),
            ]);
        }

        // Order Status Distribution
        $orderStatusDistribution = [
            ['status' => 'Pending', 'count' => Order::where('status', 'pending')->count()],
            ['status' => 'Scheduled', 'count' => Order::where('status', 'scheduled')->count()],
            ['status' => 'In Progress', 'count' => Order::where('status', 'in_progress')->count()],
            ['status' => 'Completed', 'count' => Order::where('status', 'completed')->count()],
        ];

        return Inertia::render('dashboard', [
            'stats' => [
                'orders' => [
                    'total' => $totalOrders,
                    'pending' => $pendingOrders,
                    'completed' => $completedOrders,
                    'totalQty' => $totalOrderQty,
                ],
                'schedules' => [
                    'total' => $totalSchedules,
                    'active' => $activeSchedules,
                    'completed' => $completedSchedules,
                    'delayed' => $delayedSchedules,
                ],
                'lines' => [
                    'total' => $totalLines,
                    'active' => $activeLines,
                    'capacity' => $totalCapacity,
                ],
                'production' => [
                    'targetQty' => $totalTargetQty,
                    'completedQty' => $totalCompletedQty,
                    'completionPercentage' => $completionPercentage,
                ],
                'today' => [
                    'target' => $todayTarget,
                    'actual' => $todayActual,
                    'achievement' => $todayAchievement,
                ],
            ],
            'recentSchedules' => $recentSchedules,
            'lineUtilization' => $lineUtilization,
            'weeklyTrend' => $weeklyTrend,
            'orderStatusDistribution' => $orderStatusDistribution,
        ]);
    }
}
