<?php

namespace App\Http\Controllers\Production;

use App\Helpers\DataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Production\StoreLineRequest;
use App\Http\Requests\Production\UpdateLineRequest;
use App\Services\LineService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LineController extends Controller
{
    public function __construct(
        private LineService $lineService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Production/Line/Index');
    }

    /**
     * DataTables JSON endpoint
     */
    public function json(Request $request)
    {
        $query = \App\Models\Line::query();

        if ($request->filled('search.value')) {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        return DataTable::paginate($query, $request);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Production/Line/Form', [
            'editMode' => false,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreLineRequest $request)
    {
        try {
            $this->lineService->createLine($request->validated());

            return redirect()->route('production.lines.index')
                ->with('success', 'Line created successfully');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id)
    {
        $line = $this->lineService->getLine($id);

        return Inertia::render('Production/Line/Form', [
            'editMode' => true,
            'line' => $line,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLineRequest $request, int $id)
    {
        try {
            $this->lineService->updateLine($id, $request->validated());

            return redirect()->route('production.lines.index')
                ->with('success', 'Line updated successfully');
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
            $this->lineService->deleteLine($id);

            return redirect()->route('production.lines.index')
                ->with('success', 'Line deleted successfully');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
