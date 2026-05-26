<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStudentRequest;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * ============================================================
 *  StudentController
 * ============================================================
 *
 * GUIDE: Manages the full CRUD lifecycle for Student records.
 *
 * A "student" in this system is a registered patient of the clinic.
 * Students must be registered first before a visit can be recorded for them.
 *
 * Route::resource('students', StudentController::class) in web.php
 * maps all 7 methods below to the correct HTTP verbs and URLs automatically.
 *
 * IMPORTANT — UpdateStudentRequest:
 *   The update() method uses StoreStudentRequest but with a twist:
 *   the 'student_id' unique rule must IGNORE the current record,
 *   otherwise editing a student will fail validation because the
 *   student_id already exists (for themselves).
 *   See StoreStudentRequest::rules() for how this is handled.
 * ============================================================
 */
class StudentController extends Controller
{
    /**
     * List all students with optional search.
     *
     * Supports searching by name, student_id, grade level, or section.
     * Results are paginated so the page doesn't crash with 1000+ students.
     *
     * @param  Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $userId = (int) $request->user()->id;

        $query = Student::with('latestVisit')
                        ->ownedBy($userId)
                        ->withCount('visits') // adds a visits_count column — useful for display
                        ->orderBy('name');

        // Search across multiple columns using a single search box.
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

        // 10 students per page. withQueryString() keeps filters in pagination links.
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

    /**
     * Show the form to register a new student.
     *
     * No data needs to be loaded — the form is static HTML.
     *
     * @return View
     */
    public function create(): View
    {
        return view('students.create');
    }

    /**
     * Save a newly registered student.
     *
     * Validation is handled by StoreStudentRequest before this runs.
     * $request->validated() returns only the safe, validated fields.
     *
     * @param  StoreStudentRequest $request
     * @return RedirectResponse
     */
    public function store(StoreStudentRequest $request): RedirectResponse
    {
        // Create the student record from the validated data.
        // Mass assignment is safe here because $fillable is set in the Student model.
        Student::create([
            ...$request->validated(),
            'user_id' => $request->user()->id,
        ]);

        return redirect()
            ->route('students.index')
            ->with('success', 'Student registered successfully.');
    }

    /**
     * Display a single student's profile and visit history.
     *
     * We load the student's visits (newest first) so the view
     * can show a complete visit history table.
     *
     * @param  Student $student  Auto-resolved by Laravel from the URL {student}
     * @return View
     */
    public function show(Student $student): View
    {
        $this->authorizeStudent($student);

        // Load visits relationship, sorted newest first, paginated.
        // Using loadMissing() avoids reloading if already loaded elsewhere.
        $visits = $student->visits()
            ->ownedBy((int) auth()->id())
            ->orderByDesc('visited_at')
            ->paginate(10);

        return view('students.show', compact('student', 'visits'));
    }

    /**
     * Show the form to edit an existing student's details.
     *
     * @param  Student $student
     * @return View
     */
    public function edit(Student $student): View
    {
        $this->authorizeStudent($student);

        // Pass the student to the view so the form can pre-fill with current data.
        // In Blade: value="{{ old('name', $student->name) }}"
        // old() returns the previously submitted value if validation failed,
        // otherwise falls back to the model's current value.
        return view('students.edit', compact('student'));
    }

    /**
     * Save changes to an existing student.
     *
     * The 'student_id' unique rule in StoreStudentRequest uses
     * Rule::unique()->ignore($this->student) to skip the current record,
     * preventing a false duplicate error on update.
     *
     * @param  StoreStudentRequest $request
     * @param  Student             $student
     * @return RedirectResponse
     */
    public function update(StoreStudentRequest $request, Student $student): RedirectResponse
    {
        $this->authorizeStudent($student);

        // update() fills only the $fillable columns from validated data.
        $student->update($request->validated());

        return redirect()
            ->route('students.show', $student)
            ->with('success', 'Student record updated successfully.');
    }

    /**
     * Delete a student and all their visit records.
     *
     * IMPORTANT: The visits table has:
     *   $table->foreignId('student_id')->constrained()->cascadeOnDelete();
     *
     * This means the database automatically deletes all visits for this student
     * when the student is deleted. No extra PHP code needed.
     *
     * WARNING: This is permanent. Consider a "soft delete" approach for production:
     *   Add SoftDeletes trait to Student model + use $table->softDeletes() in migration.
     *   Then $student->delete() just sets deleted_at instead of removing the row.
     *
     * @param  Student $student
     * @return RedirectResponse
     */
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
