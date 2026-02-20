<?php

namespace App\Http\Controllers\Production;

use App\Helpers\DataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Production\StoreOrderRequest;
use App\Http\Requests\Production\UpdateOrderRequest;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function __construct(
        private OrderService $orderService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Production/Order/Index');
    }

    /**
     * DataTables JSON endpoint
     */
    public function json(Request $request)
    {
        $query = \App\Models\Order::query();

        if ($request->filled('search.value')) {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('product_name', 'like', "%{$search}%")
                    ->orWhere('customer', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        return DataTable::paginate($query, $request);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Production/Order/Form', [
            'editMode' => false,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreOrderRequest $request)
    {
        try {
            $this->orderService->createOrder($request->validated());

            return redirect()->route('production.orders.index')
                ->with('success', 'Order created successfully');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id)
    {
        $order = $this->orderService->getOrder($id);

        // Format dates for HTML date inputs
        $formattedOrder = $order->toArray();
        $formattedOrder['order_date'] = $order->order_date?->format('Y-m-d');
        $formattedOrder['due_date'] = $order->due_date?->format('Y-m-d');

        return Inertia::render('Production/Order/Form', [
            'editMode' => true,
            'order' => $formattedOrder,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateOrderRequest $request, int $id)
    {
        try {
            $this->orderService->updateOrder($id, $request->validated());

            return redirect()->route('production.orders.index')
                ->with('success', 'Order updated successfully');
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
            $this->orderService->deleteOrder($id);

            return redirect()->route('production.orders.index')
                ->with('success', 'Order deleted successfully');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
