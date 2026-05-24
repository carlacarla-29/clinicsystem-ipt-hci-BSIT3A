<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900">Students</h1>
                <p class="mt-1 text-sm text-gray-500">Manage and view all student records.</p>
            </div>

            <a href="{{ route('students.create') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-primary px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-primary-dark">
                <span class="text-lg leading-none">+</span>
                <span>Add New Student</span>
            </a>
        </div>
    </x-slot>

    <div class="pb-8">
        <div class="mx-auto max-w-7xl space-y-5 px-4 sm:px-6 lg:px-10">
            @if(session('success'))
                <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            <div class="students-stat-grid">
                <div class="students-stat-card">
                    <div class="students-stat-icon students-stat-icon-green">♙</div>
                    <div>
                        <p class="students-stat-label">Total Students</p>
                        <p class="students-stat-value">{{ number_format($totalStudents) }}</p>
                        <p class="students-stat-note">All registered students</p>
                    </div>
                </div>

                <div class="students-stat-card">
                    <div class="students-stat-icon students-stat-icon-purple">▾</div>
                    <div>
                        <p class="students-stat-label">New This Month</p>
                        <p class="students-stat-value">{{ number_format($newThisMonth) }}</p>
                        <p class="students-stat-note">Newly registered</p>
                    </div>
                </div>

                <div class="students-stat-card">
                    <div class="students-stat-icon students-stat-icon-blue">⊞</div>
                    <div>
                        <p class="students-stat-label">Students Seen Today</p>
                        <p class="students-stat-value">{{ number_format($studentsSeenToday) }}</p>
                        <p class="students-stat-note">As of today</p>
                    </div>
                </div>

                <div class="students-stat-card">
                    <div class="students-stat-icon students-stat-icon-amber">↗</div>
                    <div>
                        <p class="students-stat-label">Frequent Visitors</p>
                        <p class="students-stat-value">{{ number_format($frequentVisitors) }}</p>
                        <p class="students-stat-note">Visited 2+ times</p>
                    </div>
                </div>
            </div>

            <div class="students-panel">
                <form method="GET" action="{{ route('students.index') }}" class="students-filter-bar">
                    <div class="students-search">
                        <span class="students-search-icon">⌕</span>
                        <input
                            type="text"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Search by name, ID, grade, or section..."
                        >
                    </div>

                    <button type="submit" class="students-filter-button">
                        <span>▽</span>
                        <span>Filter</span>
                    </button>

                    <label class="students-select-wrap">
                        <span>Grade</span>
                        <select name="grade_level">
                            <option value="">All Grades</option>
                            @foreach($gradeLevels as $grade)
                                <option value="{{ $grade }}" @selected(request('grade_level') === $grade)>{{ $grade }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="students-select-wrap">
                        <span>Section</span>
                        <select name="section">
                            <option value="">All Sections</option>
                            @foreach($sections as $section)
                                <option value="{{ $section }}" @selected(request('section') === $section)>{{ $section }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="students-select-wrap">
                        <span>Status</span>
                        <select name="status">
                            <option value="">All Statuses</option>
                            <option value="treated" @selected(request('status') === 'treated')>Treated</option>
                            <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                            <option value="referred" @selected(request('status') === 'referred')>Referred</option>
                        </select>
                    </label>

                    @if(request()->hasAny(['search', 'grade_level', 'section', 'status']))
                        <a href="{{ route('students.index') }}" class="students-clear-button">Clear</a>
                    @endif
                </form>

                <div class="students-table-wrap">
                    <table class="students-table">
                        <thead>
                            <tr>
                                <th>Student ID</th>
                                <th>Name</th>
                                <th>Grade & Section</th>
                                <th>Gender</th>
                                <th>Visits</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($students as $student)
                                @php
                                    $initials = collect(explode(' ', $student->name))
                                        ->filter()
                                        ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
                                        ->take(2)
                                        ->implode('');
                                    $visitStatus = $student->latestVisit?->status ?? 'pending';
                                    $avatarColors = ['students-avatar-blue', 'students-avatar-pink', 'students-avatar-purple', 'students-avatar-amber', 'students-avatar-green'];
                                @endphp
                                <tr>
                                    <td class="students-id">{{ $student->student_id }}</td>
                                    <td>
                                        <div class="students-name-cell">
                                            <div class="students-avatar {{ $avatarColors[$loop->index % count($avatarColors)] }}">{{ $initials ?: 'ST' }}</div>
                                            <span>{{ $student->name }}</span>
                                        </div>
                                    </td>
                                    <td class="students-muted">{{ $student->grade_level }} - {{ $student->section }}</td>
                                    <td>
                                        <span class="students-gender students-gender-{{ $student->gender === 'female' ? 'female' : 'male' }}">
                                            {{ ucfirst($student->gender) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="students-visit-pill">{{ $student->visits_count }}</span>
                                    </td>
                                    <td>
                                        <span class="students-status students-status-{{ $visitStatus }}">
                                            <span></span>
                                            {{ ucfirst($visitStatus) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="students-actions">
                                            <a href="{{ route('students.show', $student) }}" title="View">⊙</a>
                                            <a href="{{ route('students.edit', $student) }}" title="Edit">♢</a>
                                            <form method="POST" action="{{ route('students.destroy', $student) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" title="Delete">⋮</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="students-empty">No students found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="students-status-guide">
                    <span class="students-guide-title">Student Status Guide:</span>
                    <span><i class="students-dot students-dot-treated"></i> Treated - Visit was completed</span>
                    <span><i class="students-dot students-dot-pending"></i> Pending - Visit still needs attention</span>
                    <span><i class="students-dot students-dot-referred"></i> Referred - Student was referred for further care</span>
                </div>

                <div class="students-pagination">
                    <p>Showing {{ $students->firstItem() ?? 0 }} to {{ $students->lastItem() ?? 0 }} of {{ $students->total() }} results</p>
                    <div>{{ $students->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
