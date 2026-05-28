<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStudentRequest;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function index(Request $request): View
    {
        $userId = (int) $request->user()->id;

        $query = Student::with('latestVisit')
                        ->ownedBy($userId)
                        ->withCount('visits') // adds a visits_count column — useful for display
                        ->orderBy('name');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name',        'like', "%{$search}%")
                  ->orWhere('student_id', 'like', "%{$search}%")
                  ->orWhere('grade_level','like', "%{$search}%")
                  ->orWhere('section',    'like', "%{$search}%");
            });
        }

        if ($request->filled('grade_level')) {
            $query->where('grade_level', $request->grade_level);
        }

        if ($request->filled('section')) {
            $query->where('section', $request->section);
        }

        if (in_array($request->status, ['treated', 'pending', 'referred'], true)) {
            $query->where(function ($query) use ($request) {
                $query->whereHas('latestVisit', fn ($visitQuery) =>
                    $visitQuery->where('status', $request->status)
                );

                if ($request->status === 'pending') {
                    $query->orWhereDoesntHave('visits');
                }
            });
        }

        $totalStudents = Student::ownedBy($userId)->count();
        $newThisMonth = Student::ownedBy($userId)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        $studentsSeenToday = Student::ownedBy($userId)->whereHas('visits', fn ($query) =>
            $query->ownedBy($userId)->whereDate('visited_at', today())
        )->count();
        $frequentVisitors = Student::ownedBy($userId)->has('visits', '>=', 2)->count();

        $gradeLevels = Student::select('grade_level')
            ->ownedBy($userId)
            ->whereNotNull('grade_level')
            ->distinct()
            ->orderBy('grade_level')
            ->pluck('grade_level');
        $sections = Student::select('section')
            ->ownedBy($userId)
            ->whereNotNull('section')
            ->when($request->filled('grade_level'), fn ($query) =>
                $query->where('grade_level', $request->grade_level)
            )
            ->distinct()
            ->orderBy('section')
            ->pluck('section');

        $students = $query->paginate(10)->withQueryString();

        return view('students.index', compact(
            'students',
            'totalStudents',
            'newThisMonth',
            'studentsSeenToday',
            'frequentVisitors',
            'gradeLevels',
            'sections'
        ));
    }

    public function create(): View
    {
        return view('students.create');
    }

    public function store(StoreStudentRequest $request): RedirectResponse
    {
        Student::create([
            ...$request->validated(),
            'user_id' => $request->user()->id,
        ]);

        return redirect()
            ->route('students.index')
            ->with('success', 'Student registered successfully.');
    }

    public function show(Student $student): View
    {
        $this->authorizeStudent($student);

        $visits = $student->visits()
            ->ownedBy((int) auth()->id())
            ->orderByDesc('visited_at')
            ->paginate(10);

        return view('students.show', compact('student', 'visits'));
    }

    public function edit(Student $student): View
    {
        $this->authorizeStudent($student);

        return view('students.edit', compact('student'));
    }

    public function update(StoreStudentRequest $request, Student $student): RedirectResponse
    {
        $this->authorizeStudent($student);

        $student->update($request->validated());

        return redirect()
            ->route('students.show', $student)
            ->with('success', 'Student record updated successfully.');
    }

    public function destroy(Student $student): RedirectResponse
    {
        $this->authorizeStudent($student);

        $student->delete();

        return redirect()
            ->route('students.index')
            ->with('success', 'Student and all related visits have been deleted.');
    }

    private function authorizeStudent(Student $student): void
    {
        abort_unless($student->user_id === auth()->id(), 404);
    }
}
