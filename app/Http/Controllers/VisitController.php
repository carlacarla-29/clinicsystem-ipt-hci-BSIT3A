<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVisitRequest;
use App\Models\Medicine;
use App\Models\Student;
use App\Models\Visit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

/**
 * ============================================================
 *  VisitController
 * ============================================================
 *
 * GUIDE: What is a Resource Controller?
 * ----------------------------------------
 * A resource controller handles all 7 standard CRUD operations
 * for a single model. Laravel wires these automatically when you
 * declare Route::resource('visits', VisitController::class) in web.php.
 *
 * METHOD → HTTP VERB + URL           → PURPOSE
 * -------------------------------------------------------
 * index()   GET    /visits            List all visits (with search/filter)
 * create()  GET    /visits/create     Show the "record new visit" form
 * store()   POST   /visits            Save a new visit to the database
 * show()    GET    /visits/{visit}    View one visit in detail
 * edit()    GET    /visits/{visit}/edit  Show the edit form
 * update()  PUT    /visits/{visit}    Save changes to an existing visit
 * destroy() DELETE /visits/{visit}    Delete a visit
 *
 * ADDITIONAL (custom routes in web.php):
 * exportCsv() GET  /visits/export/csv  Download visits as a CSV file
 * exportPdf() GET  /visits/export/pdf  Download visits as a PDF
 *
 * ROUTE MODEL BINDING:
 *   When a method receives `Visit $visit` as a parameter, Laravel
 *   automatically runs Visit::findOrFail($id) using the {visit} in the URL.
 *   If the ID doesn't exist, a 404 is returned automatically — no extra code needed.
 *
 * EAGER LOADING (->with()):
 *   Always use ->with('student') when listing visits to avoid the N+1 query problem.
 *   N+1 means: 1 query to get visits, then N more queries (one per visit) to get each student.
 *   ->with('student') collapses this into exactly 2 queries total.
 * ============================================================
 */
class VisitController extends Controller
{
    /**
     * Display a paginated, searchable, filterable list of visits.
     *
     * FEATURES:
     *   - Search by student name or student ID
     *   - Filter by date range (date_from / date_to)
     *   - Results are paginated (15 per page)
     *   - Sorted newest first
     *
     * URL EXAMPLES:
     *   /visits
     *   /visits?search=Juan
     *   /visits?date_from=2024-05-01&date_to=2024-05-31
     *   /visits?search=Juan&date_from=2024-05-01
     *
     * @param  Request $request  Contains query string params: search, date_from, date_to
     * @return View
     */
    public function index(Request $request): View
    {
        $userId = (int) $request->user()->id;

        // Start building the query — we'll add conditions based on the request.
        // with('student') eager-loads the related student for each visit (avoids N+1).
        $period = $request->input('period', 'today');
        $period = in_array($period, ['today', 'week', 'month', 'custom'], true) ? $period : 'today';

        if ($request->filled('date_from') || $request->filled('date_to')) {
            $period = 'custom';
        }

        $query = Visit::with('student')
            ->ownedBy($userId)
            ->orderByDesc('visited_at');

        // --- Search Filter ---
        // $request->filled('search') is true only if 'search' is present AND not empty.
        // This is safer than $request->has() which returns true even for empty strings.
        if ($request->filled('search')) {
            $search = $request->search;

            // whereHas() adds a subquery condition on the related 'student' model.
            // We search both the student's name and their school-assigned student_id.
            // The fn($q) => ... is a PHP arrow function / closure passed to whereHas.
            $query->where(function ($query) use ($search) {
                $query->where('complaint', 'like', "%{$search}%")
                    ->orWhereHas('student', fn ($studentQuery) =>
                        $studentQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('student_id', 'like', "%{$search}%")
                    );
            });
        }

        // --- Date Range Filter ---
        // whereDate() compares only the DATE portion of a datetime column,
        // ignoring the time. So '2024-05-01 08:30:00' matches date '2024-05-01'.
        if ($period === 'today') {
            $query->whereDate('visited_at', today());
        } elseif ($period === 'week') {
            $query->whereBetween('visited_at', [now()->startOfWeek(), now()->endOfWeek()]);
        } elseif ($period === 'month') {
            $query->whereMonth('visited_at', now()->month)
                ->whereYear('visited_at', now()->year);
        } else {
            if ($request->filled('date_from')) {
                $query->whereDate('visited_at', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('visited_at', '<=', $request->date_to);
            }
        }

        $totalVisits = (clone $query)->count();
        $treatedVisits = (clone $query)->where('status', 'treated')->count();
        $pendingVisits = (clone $query)->where('status', 'pending')->count();
        $referredVisits = (clone $query)->where('status', 'referred')->count();

        if (in_array($request->status, ['treated', 'pending', 'referred'], true)) {
            $query->where('status', $request->status);
        }

        // paginate(15) = show 15 records per page and generate pagination links.
        // withQueryString() preserves search/filter params in the pagination links.
        // Without it, clicking "Next page" would lose your search term.
        $visits = $query->paginate(10)->withQueryString();

        // Count today's visits separately for a stat badge in the view.
        $todayCount = Visit::ownedBy($userId)->whereDate('visited_at', today())->count();

        return view('visits.index', compact(
            'visits',
            'todayCount',
            'period',
            'totalVisits',
            'treatedVisits',
            'pendingVisits',
            'referredVisits'
        ));
    }

    /**
     * Show the form for recording a new visit.
     *
     * We pass all students and available medicines to the view
     * so the form can render dropdown selects.
     *
     * @return View
     */
    public function create(): View
    {
        $userId = (int) auth()->id();

        // Get all students for the dropdown, sorted alphabetically.
        $students = Student::ownedBy($userId)->orderBy('name')->get();

        // Only show medicines that still have stock (quantity > 0).
        // No point showing a medicine you can't dispense.
        $medicines = Medicine::ownedBy($userId)->where('quantity', '>', 0)->orderBy('name')->get();

        return view('visits.create', compact('students', 'medicines'));
    }

    /**
     * Save a new visit to the database.
     *
     * GUIDE: Why StoreVisitRequest instead of Request?
     * -------------------------------------------------
     * StoreVisitRequest is a FormRequest class (in app/Http/Requests/).
     * Laravel automatically validates the input using that class's rules()
     * method BEFORE this method is even called. If validation fails,
     * Laravel redirects back with errors — no code needed here for that.
     *
     * $request->validated() returns only the fields that passed validation,
     * preventing any unexpected/malicious fields from being saved.
     *
     * @param  StoreVisitRequest $request  Pre-validated request data
     * @return RedirectResponse
     */
    public function store(StoreVisitRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        unset($validated['medicines']);

        // Create the visit. We merge in recorded_by (the currently logged-in user's ID)
        // because that field isn't in the form — it's set server-side for security.
        // auth()->id() returns the ID of the currently authenticated user.
        $visit = Visit::create([
            ...$validated,   // spread all validated fields
            'recorded_by' => auth()->id(),
        ]);

        // --- Handle Medicine Dispensing (Additional Feature) ---
        // If the form submitted medicines, attach them to this visit
        // and deduct the quantities from inventory stock.
        //
        // Expected form structure:
        //   medicines[1] = 3   (medicine ID 1, quantity 3)
        //   medicines[4] = 1   (medicine ID 4, quantity 1)
        if ($request->filled('medicines')) {
            $syncData = [];

            foreach ($request->medicines as $medicineId => $qty) {
                // Validate that the medicine exists and has enough stock.
                $medicine = Medicine::ownedBy((int) auth()->id())->find($medicineId);

                if ($medicine && $medicine->quantity >= $qty && $qty > 0) {
                    // Store for the pivot table: quantity_given per medicine
                    $syncData[$medicineId] = ['quantity_given' => $qty];

                    // Deduct from inventory. decrement() is an atomic DB operation
                    // (safer than: $medicine->quantity -= $qty; $medicine->save()).
                    $medicine->decrement('quantity', $qty);
                }
            }

            // sync() attaches medicines to this visit in the visit_medicines pivot table.
            $visit->medicines()->sync($syncData);
        }

        // Redirect to the visits list with a success flash message.
        // The 'success' key is available in Blade via session('success').
        return redirect()
            ->route('visits.index')
            ->with('success', 'Visit recorded successfully.');
    }

    /**
     * Display a single visit's full details.
     *
     * Route model binding automatically resolves the {visit} URL segment
     * to a Visit model instance. No need to manually call Visit::find($id).
     *
     * We eager-load related models so the view can display:
     *   $visit->student->name
     *   $visit->recorder->name
     *   $visit->medicines (Collection of Medicine models)
     *
     * @param  Visit $visit  Auto-resolved by Laravel from the URL
     * @return View
     */
    public function show(Visit $visit): View
    {
        $this->authorizeVisit($visit);

        // Load all relationships needed by the view in one go.
        $visit->load('student', 'recorder', 'medicines');

        return view('visits.show', compact('visit'));
    }

    /**
     * Show the form to edit an existing visit.
     *
     * @param  Visit $visit
     * @return View
     */
    public function edit(Visit $visit): View
    {
        $this->authorizeVisit($visit);

        $visit->load('medicines'); // load currently attached medicines

        $userId = (int) auth()->id();
        $students  = Student::ownedBy($userId)->orderBy('name')->get();
        $medicines = Medicine::ownedBy($userId)->orderBy('name')->get(); // all medicines for the form

        return view('visits.edit', compact('visit', 'students', 'medicines'));
    }

    /**
     * Save changes to an existing visit.
     *
     * Uses the same StoreVisitRequest validation rules as store().
     * You could create a separate UpdateVisitRequest if the update rules differ.
     *
     * @param  StoreVisitRequest $request
     * @param  Visit             $visit    Auto-resolved visit to update
     * @return RedirectResponse
     */
    public function update(StoreVisitRequest $request, Visit $visit): RedirectResponse
    {
        $this->authorizeVisit($visit);
        $validated = $request->validated();
        unset($validated['medicines']);

        // Update only the validated fields on the existing model.
        $visit->update($validated);

        return redirect()
            ->route('visits.index')
            ->with('success', 'Visit updated successfully.');
    }

    /**
     * Delete a visit record.
     *
     * Because of cascadeOnDelete() on the visit_medicines foreign key,
     * all related medicine pivot records are automatically deleted by the DB.
     * Note: we do NOT restore the medicine stock on deletion — adjust if needed.
     *
     * @param  Visit $visit
     * @return RedirectResponse
     */
    public function destroy(Visit $visit): RedirectResponse
    {
        $this->authorizeVisit($visit);

        $visit->delete();

        return redirect()
            ->route('visits.index')
            ->with('success', 'Visit deleted successfully.');
    }

    // ============================================================
    // ADDITIONAL FEATURE: Export to CSV
    // ============================================================

    /**
     * Stream a CSV download of visit records.
     *
     * GUIDE: How streaming CSV works
     * --------------------------------
     * Instead of building the entire file in memory and then sending it,
     * response()->stream() sends the output in chunks as it's generated.
     * This is memory-efficient and works for very large datasets.
     *
     * URL: GET /visits/export/csv
     * URL with filters: /visits/export/csv?date_from=2024-05-01&date_to=2024-05-31
     *
     * @param  Request $request
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function exportCsv(Request $request)
    {
        $userId = (int) $request->user()->id;

        // Build the query with optional date filters (same as index()).
        $query = Visit::with('student')
            ->ownedBy($userId)
            ->orderByDesc('visited_at');

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($query) use ($search) {
                $query->where('complaint', 'like', "%{$search}%")
                    ->orWhereHas('student', fn ($studentQuery) =>
                        $studentQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('student_id', 'like', "%{$search}%")
                    );
            });
        }

        $period = $request->input('period', 'today');
        $period = in_array($period, ['today', 'week', 'month', 'custom'], true) ? $period : 'today';

        if ($request->filled('date_from') || $request->filled('date_to')) {
            $period = 'custom';
        }

        if ($period === 'today') {
            $query->whereDate('visited_at', today());
        } elseif ($period === 'week') {
            $query->whereBetween('visited_at', [now()->startOfWeek(), now()->endOfWeek()]);
        } elseif ($period === 'month') {
            $query->whereMonth('visited_at', now()->month)
                ->whereYear('visited_at', now()->year);
        } else {
            if ($request->filled('date_from')) {
                $query->whereDate('visited_at', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('visited_at', '<=', $request->date_to);
            }
        }

        if (in_array($request->status, ['treated', 'pending', 'referred'], true)) {
            $query->where('status', $request->status);
        }

        $visits = $query->get();

        // HTTP headers that tell the browser to download the response as a file.
        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="clinic-visits-' . now()->format('Y-m-d') . '.csv"',
        ];

        // The callback is called by Laravel to generate the response body.
        // fopen('php://output', 'w') writes directly to the HTTP response stream.
        $callback = function () use ($visits) {
            $file = fopen('php://output', 'w');

            // Write the header row
            fputcsv($file, ['Date & Time', 'Student ID', 'Student Name', 'Grade & Section', 'Complaint', 'Diagnosis', 'Treatment', 'Status', 'Recorded By']);

            // Write one row per visit
            foreach ($visits as $visit) {
                fputcsv($file, [
                    $visit->visited_at->format('Y-m-d H:i'),
                    $visit->student->student_id ?? 'N/A',
                    $visit->student->name ?? 'N/A',
                    ($visit->student->grade_level ?? '') . ' - ' . ($visit->student->section ?? ''),
                    $visit->complaint,
                    $visit->diagnosis ?? '',
                    $visit->treatment ?? '',
                    $visit->status,
                    $visit->recorder->name ?? 'N/A',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export visits to PDF.
     *
     * GUIDE: For PDF export you need a package like barryvdh/laravel-dompdf.
     * Install it with: composer require barryvdh/laravel-dompdf
     *
     * Then replace the placeholder below with:
     *   $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('visits.pdf', compact('visits'));
     *   return $pdf->download('clinic-visits.pdf');
     *
     * For now this returns a simple response as a placeholder.
     *
     * @param  Request $request
     * @return RedirectResponse
     */
    public function exportPdf(Request $request): RedirectResponse
    {
        // TODO: Install barryvdh/laravel-dompdf then implement PDF generation.
        // composer require barryvdh/laravel-dompdf
        return redirect()
            ->route('visits.index')
            ->with('info', 'PDF export coming soon. Install barryvdh/laravel-dompdf to enable.');
    }

    private function authorizeVisit(Visit $visit): void
    {
        abort_unless($visit->recorded_by === auth()->id(), 404);
    }
}
