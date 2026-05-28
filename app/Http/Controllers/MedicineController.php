<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MedicineController extends Controller
{
    public function index(Request $request): View
    {
        $userId = (int) $request->user()->id;
        $query = Medicine::ownedBy($userId)->orderBy('name');

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('unit', 'like', "%{$search}%");
            });
        }

        if ($request->filled('unit')) {
            $query->where('unit', $request->unit);
        }

        if ($request->stock_status === 'in_stock') {
            $query->where('quantity', '>', 10);
        } elseif ($request->stock_status === 'low_stock') {
            $query->whereBetween('quantity', [1, 10]);
        } elseif ($request->stock_status === 'out_of_stock') {
            $query->where('quantity', '<=', 0);
        }

        $totalMedicines = Medicine::ownedBy($userId)->count();
        $lowStockCount = Medicine::ownedBy($userId)->whereBetween('quantity', [1, 10])->count();
        $outOfStockCount = Medicine::ownedBy($userId)->where('quantity', '<=', 0)->count();
        $inStockCount = Medicine::ownedBy($userId)->where('quantity', '>', 10)->count();
        $dispensedToday = DB::table('visit_medicines')
            ->join('visits', 'visit_medicines.visit_id', '=', 'visits.id')
            ->join('medicines', 'visit_medicines.medicine_id', '=', 'medicines.id')
            ->where('visits.recorded_by', $userId)
            ->where('medicines.user_id', $userId)
            ->whereDate('visits.visited_at', today())
            ->sum('visit_medicines.quantity_given');

        $units = Medicine::select('unit')
            ->ownedBy($userId)
            ->whereNotNull('unit')
            ->distinct()
            ->orderBy('unit')
            ->pluck('unit');

        $medicines = $query->paginate(10)->withQueryString();

        $lowStock = Medicine::ownedBy($userId)
            ->whereBetween('quantity', [1, 10])
            ->orderBy('quantity')
            ->get();

        return view('medicines.index', compact(
            'medicines',
            'lowStock',
            'totalMedicines',
            'lowStockCount',
            'outOfStockCount',
            'inStockCount',
            'dispensedToday',
            'units'
        ));
    }

    public function create(): View
    {
        return view('medicines.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $userId = (int) $request->user()->id;

        $validated = $request->validate([
            'name'     => [
                'required',
                'string',
                'max:100',
                Rule::unique('medicines', 'name')->where(fn ($query) =>
                    $query->where('user_id', $userId)
                ),
            ],
            'unit'     => ['required', 'in:tablet,capsule,ml,sachet,piece'],
            'quantity' => ['required', 'integer', 'min:0'],
        ], [
            'name.unique'   => 'A medicine with this name already exists.',
            'unit.in'       => 'Unit must be: tablet, capsule, ml, sachet, or piece.',
            'quantity.min'  => 'Quantity cannot be negative.',
        ]);

        Medicine::create([
            ...$validated,
            'user_id' => $userId,
        ]);

        return redirect()
            ->route('medicines.index')
            ->with('success', 'Medicine added to inventory.');
    }

    public function show(Medicine $medicine): View
    {
        $this->authorizeMedicine($medicine);

        $medicine->load(['visits' => fn ($query) =>
            $query->ownedBy((int) auth()->id())->with('student')
        ]);

        return view('medicines.show', compact('medicine'));
    }

    public function edit(Medicine $medicine): View
    {
        $this->authorizeMedicine($medicine);

        return view('medicines.edit', compact('medicine'));
    }

    public function update(Request $request, Medicine $medicine): RedirectResponse
    {
        $this->authorizeMedicine($medicine);
        $userId = (int) $request->user()->id;

        $validated = $request->validate([
            'name'     => [
                'required',
                'string',
                'max:100',
                Rule::unique('medicines', 'name')
                    ->where(fn ($query) => $query->where('user_id', $userId))
                    ->ignore($medicine->id),
            ],
            'unit'     => ['required', 'in:tablet,capsule,ml,sachet,piece'],
            'quantity' => ['required', 'integer', 'min:0'],
        ], [
            'name.unique'  => 'Another medicine already has this name.',
            'quantity.min' => 'Quantity cannot be negative.',
        ]);

        $medicine->update($validated);

        return redirect()
            ->route('medicines.index')
            ->with('success', 'Medicine updated successfully.');
    }

    public function destroy(Medicine $medicine): RedirectResponse
    {
        $this->authorizeMedicine($medicine);

        $medicine->delete();

        return redirect()
            ->route('medicines.index')
            ->with('success', 'Medicine removed from inventory.');
    }

    private function authorizeMedicine(Medicine $medicine): void
    {
        abort_unless($medicine->user_id === auth()->id(), 404);
    }
}
