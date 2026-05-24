<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use App\Models\Student;
use App\Models\Visit;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * ============================================================
 *  DashboardController
 * ============================================================
 *
 * GUIDE: What is the Dashboard?
 * ------------------------------
 * The dashboard is the first screen the nurse/admin sees after login.
 * It gives a quick overview of the clinic's activity:
 *
 *   - How many students visited TODAY
 *   - Total visits and students on record
 *   - A 7-day visit trend (used to draw a chart in the Blade view)
 *   - Top 5 most common complaints
 *   - Low medicine stock alerts
 *
 * QUERY GUIDE:
 *   - ::count()                → SELECT COUNT(*) FROM table
 *   - ::whereDate('col', date) → WHERE DATE(col) = 'date'
 *   - ::selectRaw(...)         → lets you write raw SQL expressions
 *   - ->groupBy(...)           → GROUP BY clause
 *   - ->pluck('val', 'key')    → returns a key=>value Collection (perfect for charts)
 * ============================================================
 */
class DashboardController extends Controller
{
    /**
     * Display the main clinic dashboard.
     *
     * All data collected here is passed to the Blade view as variables.
     * In the view, access them directly: {{ $todayVisits }}, {{ $totalStudents }}, etc.
     *
     * @return View
     */
    public function index(Request $request): View
    {
        // -------------------------------------------------------
        // STAT CARDS
        // -------------------------------------------------------

        // Count visits where the date portion of visited_at equals today.
        // today() is a Laravel helper that returns Carbon::today() (midnight of today).
        $todayVisits = Visit::whereDate('visited_at', today())->count();

        // Total visits ever recorded in the system.
        $totalVisits = Visit::count();

        // Total unique students registered in the system.
        $totalStudents = Student::count();

        $newStudentsToday = Student::whereDate('created_at', today())->count();

        $medicinesDispensedToday = DB::table('visit_medicines')
            ->join('visits', 'visit_medicines.visit_id', '=', 'visits.id')
            ->whereDate('visits.visited_at', today())
            ->sum('visit_medicines.quantity_given');

        // -------------------------------------------------------
        // 7-DAY ANALYTICS DATA
        // -------------------------------------------------------
        $dates = collect(CarbonPeriod::create(now()->subDays(6)->toDateString(), today()->toDateString()))
            ->map(fn ($date) => $date->format('Y-m-d'));

        $visitCountsByDate = Visit::selectRaw('DATE(visited_at) as date, COUNT(*) as count')
            ->where('visited_at', '>=', now()->subDays(6)->startOfDay())
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date');

        $studentCountsByDate = Student::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date');

        $medicineCountsByDate = DB::table('visit_medicines')
            ->join('visits', 'visit_medicines.visit_id', '=', 'visits.id')
            ->selectRaw('DATE(visits.visited_at) as date, SUM(visit_medicines.quantity_given) as count')
            ->where('visits.visited_at', '>=', now()->subDays(6)->startOfDay())
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date');

        $dailyVisits = $dates->map(fn ($date) => [
            'label' => Carbon::parse($date)->format('M d'),
            'count' => (int) ($visitCountsByDate[$date] ?? 0),
        ]);

        $dailyNewStudents = $dates->map(fn ($date) => [
            'label' => Carbon::parse($date)->format('M d'),
            'count' => (int) ($studentCountsByDate[$date] ?? 0),
        ]);

        $dailyMedicinesDispensed = $dates->map(fn ($date) => [
            'label' => Carbon::parse($date)->format('M d'),
            'count' => (int) ($medicineCountsByDate[$date] ?? 0),
        ]);

        $topDispensedMedicines = Medicine::query()
            ->join('visit_medicines', 'medicines.id', '=', 'visit_medicines.medicine_id')
            ->join('visits', 'visit_medicines.visit_id', '=', 'visits.id')
            ->select('medicines.name', 'medicines.unit')
            ->selectRaw('SUM(visit_medicines.quantity_given) as total_dispensed')
            ->where('visits.visited_at', '>=', now()->subDays(6)->startOfDay())
            ->groupBy('medicines.id', 'medicines.name', 'medicines.unit')
            ->orderByDesc('total_dispensed')
            ->limit(3)
            ->get();

        // -------------------------------------------------------
        // TOP 5 COMPLAINTS
        // -------------------------------------------------------
        // Groups visits by complaint text and counts how many times
        // each complaint appears. Shows the most common health issues.
        $topComplaints = Visit::query()
            ->join('students', 'visits.student_id', '=', 'students.id')
            ->select('visits.complaint')
            ->selectRaw('COUNT(*) as count')
            ->selectRaw('GROUP_CONCAT(DISTINCT students.name ORDER BY students.name SEPARATOR ", ") as student_names')
            ->where('visits.visited_at', '>=', now()->subDays(6)->startOfDay())
            ->groupBy('visits.complaint')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        // -------------------------------------------------------
        // LOW STOCK MEDICINE ALERT (Additional Feature)
        // -------------------------------------------------------
        // Flags any medicine with 10 or fewer units remaining.
        // The number 10 is a reasonable "reorder point" for a school clinic.
        $lowStockMedicines = Medicine::where('quantity', '<=', 10)
            ->orderBy('quantity')
            ->get();

        // -------------------------------------------------------
        // MEDICINES FOR DASHBOARD (quick-search / quick-list)
        // -------------------------------------------------------
        // Allow a simple `q` query parameter to filter medicines by name.
        // If no query provided, return the first 10 ordered by name.
        $medicines = Medicine::when($request->query('q'), function ($query, $q) {
                $query->where('name', 'like', "%{$q}%");
            })
            ->orderBy('name')
            ->get();

        // -------------------------------------------------------
        // RECENT VISITS (last 5)
        // -------------------------------------------------------
        // Eager-load the related student so Blade can do $visit->student->name
        // without triggering N+1 queries (one query per visit).
        // with('student') = JOIN equivalent that runs in 2 queries total.
        $recentVisits = Visit::with('student')
            ->orderByDesc('visited_at')
            ->limit(5)
            ->get();

        // Pass all variables to the view.
        // Each key becomes a variable name inside the Blade template.
        // e.g. compact('todayVisits') → the view can use {{ $todayVisits }}
        return view('dashboard', compact(
            'todayVisits',
            'totalVisits',
            'totalStudents',
            'newStudentsToday',
            'medicinesDispensedToday',
            'dailyVisits',
            'dailyNewStudents',
            'dailyMedicinesDispensed',
            'topDispensedMedicines',
            'topComplaints',
            'lowStockMedicines',
            'recentVisits',
            'medicines'
        ));
    }
}
