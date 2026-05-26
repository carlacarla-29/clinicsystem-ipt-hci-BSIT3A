<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * ============================================================
 *  MedicineController
 * ============================================================
 *
 * GUIDE: Additional Feature — Medicine Inventory Management
 * ----------------------------------------------------------
 * This controller manages the clinic's medicine stock.
 * It tracks what medicines are available and how many units remain.
 *
 * Stock is automatically REDUCED whenever a visit is recorded
 * and medicines are dispensed (handled in VisitController@store).
 *
 * The clinic admin uses this controller to:
 *   - Add new medicines to the inventory
 *   - Restock existing medicines (update quantity)
 *   - Remove medicines no longer in use
 *   - View which medicines are running low
 *
 * No separate FormRequest is used here — validation is done inline
 * using $request->validate() for simplicity. For a larger system,
 * create a StoreMedicineRequest class (same pattern as StoreVisitRequest).
 * ============================================================
 */
class MedicineController extends Controller
{
    /**
     * Display all medicines with their current stock levels.
     *
     * Highlights low-stock items (≤ 10 units) so the admin
     * knows what needs to be restocked.
     *
     * @return View
     */
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

        // Separate collection of low-stock medicines for an alert banner.
        // The view can show a warning if this collection is not empty.
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

    /**
     * Show the form to add a new medicine to inventory.
     *
     * @return View
     */
    public function create(): View
    {
        return view('medicines.create');
    }

    /**
     * Save a new medicine to the database.
     *
     * Inline validation using $request->validate() — this is equivalent
     * to a FormRequest but written directly in the controller method.
     * Use this approach for simple forms; use FormRequest for complex ones.
     *
     * @param  Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $userId = (int) $request->user()->id;

        // validate() automatically redirects back with errors if it fails.
        // The returned array contains only the validated fields.
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
            // Custom error messages for this form.
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

    /**
     * Display a single medicine's details.
     *
     * Shows stock history (visits where this medicine was dispensed).
     *
     * @param  Medicine $medicine
     * @return View
     */
    public function show(Medicine $medicine): View
    {
        $this->authorizeMedicine($medicine);

        // Load visits where this medicine was dispensed.
        // The pivot contains quantity_given for each visit.
        $medicine->load(['visits' => fn ($query) =>
            $query->ownedBy((int) auth()->id())->with('student')
        ]);

        return view('medicines.show', compact('medicine'));
    }

    /**
     * Show the form to edit a medicine (e.g. restock / rename).
     *
     * @param  Medicine $medicine
     * @return View
     */
    public function edit(Medicine $medicine): View
    {
        $this->authorizeMedicine($medicine);

        return view('medicines.edit', compact('medicine'));
    }

    /**
     * Save changes to a medicine record.
     *
     * Common use: updating the quantity after a restock delivery.
     * The admin enters the NEW total quantity (not the amount added).
     *
     * @param  Request  $request
     * @param  Medicine $medicine
     * @return RedirectResponse
     */
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
            // Ignore uniqueness check for the current medicine's own name.
            // 'unique:medicines,name,' . $medicine->id tells Laravel:
            // "this name must be unique in the medicines table, but ignore row with id = {id}"
            'name.unique'  => 'Another medicine already has this name.',
            'quantity.min' => 'Quantity cannot be negative.',
        ]);

        $medicine->update($validated);

        return redirect()
            ->route('medicines.index')
            ->with('success', 'Medicine updated successfully.');
    }

    /**
     * Remove a medicine from the inventory.
     *
     * NOTE: If this medicine has visit_medicines pivot records,
     * the delete will fail due to the foreign key constraint —
     * unless you add cascadeOnDelete() to visit_medicines.medicine_id.
     * Consider using soft deletes instead for audit purposes.
     *
     * @param  Medicine $medicine
     * @return RedirectResponse
     */
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
