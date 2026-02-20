<?php

namespace App\Http\Controllers\Production;

use App\Helpers\DataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Production\InputActualOutputRequest;
use App\Http\Requests\Production\StoreScheduleRequest;
use App\Http\Requests\Production\UpdateScheduleRequest;
use App\Services\LineService;
use App\Services\OrderService;
use App\Services\ScheduleService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ScheduleController extends Controller
{
    public function __construct(
        private ScheduleService $scheduleService,
        private LineService $lineService,
        private OrderService $orderService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $lines = $this->lineService->getActiveLines();
        $orders = $this->orderService->getSchedulableOrders();

        return Inertia::render('Production/Schedule/Index', [
            'lines' => $lines,
            'orders' => $orders,
        ]);
    }

    /**
     * DataTables JSON endpoint
     */
    public function json(Request $request)
    {
        $query = \App\Models\Schedule::with(['order', 'line'])->query();

        if ($request->filled('search.value')) {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->whereHas('order', function ($orderQuery) use ($search) {
                    $orderQuery->where('order_number', 'like', "%{$search}%")
                        ->orWhere('product_name', 'like', "%{$search}%");
                })->orWhereHas('line', function ($lineQuery) use ($search) {
                    $lineQuery->where('name', 'like', "%{$search}%");
                });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('line_id')) {
            $query->where('line_id', $request->input('line_id'));
        }

        $data = DataTable::paginate($query, $request);

        // Transform data to flatten relationships for DataTables
        $data['data'] = collect($data['data'])->map(function ($schedule) {
            $completionPercentage = $schedule->qty_total_target > 0
                ? round(($schedule->qty_completed / $schedule->qty_total_target) * 100, 1)
                : 0;

            return [
                'id' => $schedule->id,
                'order' => $schedule->order,
                'line' => $schedule->line,
                'start_date' => $schedule->start_date->format('Y-m-d'),
                'finish_date' => $schedule->finish_date->format('Y-m-d'),
                'current_finish_date' => $schedule->current_finish_date->format('Y-m-d'),
                'qty_total_target' => $schedule->qty_total_target,
                'qty_completed' => $schedule->qty_completed,
                'completion_percentage' => $completionPercentage,
                'status' => $schedule->status,
                'days_extended' => $schedule->days_extended,
            ];
        });

        return response()->json($data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $lines = $this->lineService->getActiveLines();
        $orders = $this->orderService->getSchedulableOrders();

        return Inertia::render('Production/Schedule/Form', [
            'editMode' => false,
            'lines' => $lines,
            'orders' => $orders,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreScheduleRequest $request)
    {
        try {
            $this->scheduleService->createSchedule($request->validated());

            return redirect()->route('production.schedules.index')
                ->with('success', 'Schedule created successfully with daily targets');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource (show daily outputs)
     */
    public function show(int $id)
    {
        $schedule = $this->scheduleService->getSchedule($id);
        $schedule->load(['order', 'line', 'dailyOutputs']);

        return Inertia::render('Production/Schedule/Show', [
            'schedule' => $schedule,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id)
    {
        $schedule = $this->scheduleService->getSchedule($id);
        $lines = $this->lineService->getActiveLines();
        $orders = $this->orderService->getSchedulableOrders();

        return Inertia::render('Production/Schedule/Form', [
            'editMode' => true,
            'schedule' => $schedule,
            'lines' => $lines,
            'orders' => $orders,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateScheduleRequest $request, int $id)
    {
        try {
            $this->scheduleService->updateSchedule($id, $request->validated());

            return redirect()->route('production.schedules.index')
                ->with('success', 'Schedule updated successfully');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        try {
            $this->scheduleService->deleteSchedule($id);

            return redirect()->route('production.schedules.index')
                ->with('success', 'Schedule deleted successfully');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Input actual output for a specific day
     * AJAX endpoint
     */
    public function inputActualOutput(InputActualOutputRequest $request)
    {
        try {
            $this->scheduleService->inputActualOutput(
                $request->input('daily_output_id'),
                $request->input('actual_output')
            );

            return response()->json([
                'success' => true,
                'message' => 'Actual output recorded successfully. Balancing applied if needed.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get timeline view (Gantt-like) for schedules
     * AJAX endpoint
     */
    public function timeline(Request $request)
    {
        $lineId = $request->input('line_id');

        $schedules = $lineId
            ? $this->scheduleService->getSchedulesByLine($lineId)
            : $this->scheduleService->getAllSchedules();

        return response()->json([
            'schedules' => $schedules,
        ]);
    }

    /**
     * Check line availability for date range
     * AJAX endpoint
     */
    public function checkAvailability(Request $request)
    {
        $lineId = $request->input('line_id');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $excludeScheduleId = $request->input('exclude_schedule_id');

        $isAvailable = $this->lineService->checkLineAvailability(
            $lineId,
            $startDate,
            $endDate,
            $excludeScheduleId
        );

        return response()->json([
            'available' => $isAvailable,
        ]);
    }
}
