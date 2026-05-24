@php
    $activePeriod = $period ?? request('period', 'today');
    $basePeriodQuery = request()->except(['period', 'date_from', 'date_to', 'page']);
    $percent = fn ($value) => $totalVisits > 0 ? round(($value / $totalVisits) * 100) : 0;
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="visits-header">
            <div>
                <h1>Visits</h1>
            </div>

            <a href="{{ route('visits.create') }}" class="visits-add-button">
                <span>+</span>
                <span>Add Student Visit</span>
            </a>
        </div>
    </x-slot>

    <div class="visits-page">
        <div class="visits-container">
            @if(session('success'))
                <div class="visits-alert visits-alert-success">{{ session('success') }}</div>
            @endif
            @if(session('info'))
                <div class="visits-alert visits-alert-info">{{ session('info') }}</div>
            @endif

            <div class="visits-period-tabs">
                <a href="{{ route('visits.index', array_merge($basePeriodQuery, ['period' => 'today'])) }}" class="{{ $activePeriod === 'today' ? 'is-active' : '' }}">Today</a>
                <a href="{{ route('visits.index', array_merge($basePeriodQuery, ['period' => 'week'])) }}" class="{{ $activePeriod === 'week' ? 'is-active' : '' }}">This Week</a>
                <a href="{{ route('visits.index', array_merge($basePeriodQuery, ['period' => 'month'])) }}" class="{{ $activePeriod === 'month' ? 'is-active' : '' }}">This Month</a>
                <a href="{{ route('visits.index', array_merge($basePeriodQuery, ['period' => 'custom'])) }}" class="{{ $activePeriod === 'custom' ? 'is-active' : '' }}">Custom Date</a>
            </div>

            <form method="GET" action="{{ route('visits.index') }}" class="visits-filter-panel">
                <input type="hidden" name="period" value="{{ $activePeriod }}">

                <label class="visits-field visits-search-field">
                    <span>Search</span>
                    <div class="visits-input-with-icon">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Student name or ID...">
                        <span class="visits-field-icon">Q</span>
                    </div>
                </label>

                <label class="visits-field">
                    <span>From</span>
                    <input type="date" name="date_from" value="{{ request('date_from') }}">
                </label>

                <label class="visits-field">
                    <span>To</span>
                    <input type="date" name="date_to" value="{{ request('date_to') }}">
                </label>

                <label class="visits-field">
                    <span>Status</span>
                    <select name="status">
                        <option value="">All Statuses</option>
                        <option value="treated" @selected(request('status') === 'treated')>Treated</option>
                        <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                        <option value="referred" @selected(request('status') === 'referred')>Referred</option>
                    </select>
                </label>

                <button type="submit" class="visits-filter-button">Filter</button>
                <a href="{{ route('visits.index') }}" class="visits-reset-button">
                    <span>Reset</span>
                </a>
                <a href="{{ route('visits.export.csv', request()->query()) }}" class="visits-export-button">
                    <span>Export CSV</span>
                </a>
            </form>

            <div class="visits-stat-grid">
                <div class="visits-stat-card">
                    <div class="visits-stat-icon visits-stat-teal">+</div>
                    <div>
                        <p>Total Visits</p>
                        <strong>{{ number_format($totalVisits) }}</strong>
                        <span>{{ $activePeriod === 'today' ? 'Today' : ucfirst(str_replace(['week', 'month', 'custom'], ['this week', 'this month', 'selected dates'], $activePeriod)) }}</span>
                    </div>
                </div>

                <div class="visits-stat-card">
                    <div class="visits-stat-icon visits-stat-green">OK</div>
                    <div>
                        <p>Treated</p>
                        <strong>{{ number_format($treatedVisits) }}</strong>
                        <span>{{ $percent($treatedVisits) }}%</span>
                    </div>
                </div>

                <div class="visits-stat-card">
                    <div class="visits-stat-icon visits-stat-amber">!</div>
                    <div>
                        <p>Pending</p>
                        <strong>{{ number_format($pendingVisits) }}</strong>
                        <span>{{ $percent($pendingVisits) }}%</span>
                    </div>
                </div>

                <div class="visits-stat-card">
                    <div class="visits-stat-icon visits-stat-purple">-&gt;</div>
                    <div>
                        <p>Referred</p>
                        <strong>{{ number_format($referredVisits) }}</strong>
                        <span>{{ $percent($referredVisits) }}%</span>
                    </div>
                </div>
            </div>

            <div class="visits-table-panel">
                <div class="visits-table-wrap">
                    <table class="visits-table">
                        <thead>
                            <tr>
                                <th>Date & Time</th>
                                <th>Student</th>
                                <th>Complaint</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($visits as $visit)
                                @php
                                    $studentName = $visit->student->name ?? 'N/A';
                                    $initials = collect(explode(' ', $studentName))
                                        ->filter()
                                        ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
                                        ->take(2)
                                        ->implode('');
                                    $avatarColors = ['visits-avatar-blue', 'visits-avatar-purple', 'visits-avatar-amber', 'visits-avatar-pink', 'visits-avatar-green'];
                                @endphp
                                <tr>
                                    <td>
                                        <span class="visits-date">{{ $visit->visited_at->format('M d, Y') }}</span>
                                        <span class="visits-time">{{ $visit->visited_at->format('h:i A') }}</span>
                                    </td>
                                    <td>
                                        <div class="visits-student-cell">
                                            <div class="visits-avatar {{ $avatarColors[$loop->index % count($avatarColors)] }}">{{ $initials ?: 'ST' }}</div>
                                            <div>
                                                <strong>{{ $studentName }}</strong>
                                                <span>{{ $visit->student->student_id ?? '' }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="visits-complaint">{{ $visit->complaint }}</td>
                                    <td>
                                        <span class="visits-status visits-status-{{ $visit->status }}">
                                            {{ ucfirst($visit->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="visits-actions">
                                            <a href="{{ route('visits.show', $visit) }}" title="View">View</a>
                                            <a href="{{ route('visits.edit', $visit) }}" title="Edit">Edit</a>
                                            <form method="POST" action="{{ route('visits.destroy', $visit) }}" onsubmit="return confirm('Delete this visit record?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" title="Delete">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="visits-empty">No visits found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="visits-pagination">
                    <p>Showing {{ $visits->firstItem() ?? 0 }} to {{ $visits->lastItem() ?? 0 }} of {{ $visits->total() }} results</p>
                    <div>{{ $visits->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
