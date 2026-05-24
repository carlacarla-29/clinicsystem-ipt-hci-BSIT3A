<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
    public function index(): View
    {
        // Order by name for easy scanning. All medicines, no pagination needed
        // unless the clinic has hundreds of different medicines.
        $medicines = Medicine::orderBy('name')->get();

        // Separate collection of low-stock medicines for an alert banner.
        // The view can show a warning if this collection is not empty.
        $lowStock = $medicines->where('quantity', '<=', 10);

        return view('medicines.index', compact('medicines', 'lowStock'));
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
        // validate() automatically redirects back with errors if it fails.
        // The returned array contains only the validated fields.
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:100', 'unique:medicines,name'],
            'unit'     => ['required', 'in:tablet,capsule,ml,sachet,piece'],
            'quantity' => ['required', 'integer', 'min:0'],
        ], [
            // Custom error messages for this form.
            'name.unique'   => 'A medicine with this name already exists.',
            'unit.in'       => 'Unit must be: tablet, capsule, ml, sachet, or piece.',
            'quantity.min'  => 'Quantity cannot be negative.',
        ]);

        Medicine::create($validated);

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
        // Load visits where this medicine was dispensed.
        // The pivot contains quantity_given for each visit.
        $medicine->load(['visits.student']);

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
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:100', 'unique:medicines,name,' . $medicine->id],
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
        $medicine->delete();

        return redirect()
            ->route('medicines.index')
            ->with('success', 'Medicine removed from inventory.');
    }
}
