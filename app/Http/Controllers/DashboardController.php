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

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $userId = (int) $request->user()->id;

        $todayVisits = Visit::ownedBy($userId)->whereDate('visited_at', today())->count();

        $totalVisits = Visit::ownedBy($userId)->count();

        $totalStudents = Student::ownedBy($userId)->count();

        $newStudentsToday = Student::ownedBy($userId)->whereDate('created_at', today())->count();

        $medicinesDispensedToday = DB::table('visit_medicines')
            ->join('visits', 'visit_medicines.visit_id', '=', 'visits.id')
            ->join('medicines', 'visit_medicines.medicine_id', '=', 'medicines.id')
            ->where('visits.recorded_by', $userId)
            ->where('medicines.user_id', $userId)
            ->whereDate('visits.visited_at', today())
            ->sum('visit_medicines.quantity_given');

        $dates = collect(CarbonPeriod::create(now()->subDays(6)->toDateString(), today()->toDateString()))
            ->map(fn ($date) => $date->format('Y-m-d'));

        $visitCountsByDate = Visit::ownedBy($userId)
            ->selectRaw('DATE(visited_at) as date, COUNT(*) as count')
            ->where('visited_at', '>=', now()->subDays(6)->startOfDay())
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date');

        $studentCountsByDate = Student::ownedBy($userId)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date');

        $medicineCountsByDate = DB::table('visit_medicines')
            ->join('visits', 'visit_medicines.visit_id', '=', 'visits.id')
            ->join('medicines', 'visit_medicines.medicine_id', '=', 'medicines.id')
            ->selectRaw('DATE(visits.visited_at) as date, SUM(visit_medicines.quantity_given) as count')
            ->where('visits.recorded_by', $userId)
            ->where('medicines.user_id', $userId)
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
            ->where('medicines.user_id', $userId)
            ->where('visits.recorded_by', $userId)
            ->where('visits.visited_at', '>=', now()->subDays(6)->startOfDay())
            ->groupBy('medicines.id', 'medicines.name', 'medicines.unit')
            ->orderByDesc('total_dispensed')
            ->limit(3)
            ->get();

        $topComplaints = Visit::query()
            ->join('students', 'visits.student_id', '=', 'students.id')
            ->select('visits.complaint')
            ->selectRaw('COUNT(*) as count')
            ->selectRaw('GROUP_CONCAT(DISTINCT students.name ORDER BY students.name SEPARATOR ", ") as student_names')
            ->where('visits.recorded_by', $userId)
            ->where('students.user_id', $userId)
            ->where('visits.visited_at', '>=', now()->subDays(6)->startOfDay())
            ->groupBy('visits.complaint')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        $lowStockMedicines = Medicine::ownedBy($userId)
            ->where('quantity', '<=', 10)
            ->orderBy('quantity')
            ->get();

        $medicines = Medicine::ownedBy($userId)
            ->when($request->query('q'), function ($query, $q) {
                $query->where('name', 'like', "%{$q}%");
            })
            ->orderBy('name')
            ->get();

        $recentVisits = Visit::with('student')
            ->ownedBy($userId)
            ->orderByDesc('visited_at')
            ->limit(5)
            ->get();

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
