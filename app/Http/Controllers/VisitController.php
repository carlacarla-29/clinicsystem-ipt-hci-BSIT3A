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

class VisitController extends Controller
{
    public function index(Request $request): View
    {
        $userId = (int) $request->user()->id;

        $period = $request->input('period', 'today');
        $period = in_array($period, ['today', 'week', 'month', 'custom'], true) ? $period : 'today';

        if ($request->filled('date_from') || $request->filled('date_to')) {
            $period = 'custom';
        }

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

        $visits = $query->paginate(10)->withQueryString();

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

    public function create(): View
    {
        $userId = (int) auth()->id();

        $students = Student::ownedBy($userId)->orderBy('name')->get();

        $medicines = Medicine::ownedBy($userId)->where('quantity', '>', 0)->orderBy('name')->get();

        return view('visits.create', compact('students', 'medicines'));
    }

    public function store(StoreVisitRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        unset($validated['medicines']);

        $visit = Visit::create([
            ...$validated,   // spread all validated fields
            'recorded_by' => auth()->id(),
        ]);

        if ($request->filled('medicines')) {
            $syncData = [];

            foreach ($request->medicines as $medicineId => $qty) {
                $medicine = Medicine::ownedBy((int) auth()->id())->find($medicineId);

                if ($medicine && $medicine->quantity >= $qty && $qty > 0) {
                    $syncData[$medicineId] = ['quantity_given' => $qty];

                    $medicine->decrement('quantity', $qty);
                }
            }

            $visit->medicines()->sync($syncData);
        }

        return redirect()
            ->route('visits.index')
            ->with('success', 'Visit recorded successfully.');
    }

    public function show(Visit $visit): View
    {
        $this->authorizeVisit($visit);

        $visit->load('student', 'recorder', 'medicines');

        return view('visits.show', compact('visit'));
    }

    public function edit(Visit $visit): View
    {
        $this->authorizeVisit($visit);

        $visit->load('medicines'); // load currently attached medicines

        $userId = (int) auth()->id();
        $students  = Student::ownedBy($userId)->orderBy('name')->get();
        $medicines = Medicine::ownedBy($userId)->orderBy('name')->get(); // all medicines for the form

        return view('visits.edit', compact('visit', 'students', 'medicines'));
    }

    public function update(StoreVisitRequest $request, Visit $visit): RedirectResponse
    {
        $this->authorizeVisit($visit);
        $validated = $request->validated();
        unset($validated['medicines']);

        $visit->update($validated);

        return redirect()
            ->route('visits.index')
            ->with('success', 'Visit updated successfully.');
    }

    public function destroy(Visit $visit): RedirectResponse
    {
        $this->authorizeVisit($visit);

        $visit->delete();

        return redirect()
            ->route('visits.index')
            ->with('success', 'Visit deleted successfully.');
    }

    public function exportCsv(Request $request)
    {
        $userId = (int) $request->user()->id;

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

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="clinic-visits-' . now()->format('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($visits) {
            $file = fopen('php://output', 'w');

            fputcsv($file, ['Date & Time', 'Student ID', 'Student Name', 'Grade & Section', 'Complaint', 'Diagnosis', 'Treatment', 'Status', 'Recorded By']);

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

    public function exportPdf(Request $request): RedirectResponse
    {
        return redirect()
            ->route('visits.index')
            ->with('info', 'PDF export coming soon. Install barryvdh/laravel-dompdf to enable.');
    }

    private function authorizeVisit(Visit $visit): void
    {
        abort_unless($visit->recorded_by === auth()->id(), 404);
    }
}
