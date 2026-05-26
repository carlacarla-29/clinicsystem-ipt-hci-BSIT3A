<x-app-layout>
    <x-slot name="header">
        <div class="dashboard-header">
            <div>
                <h1>Dashboard</h1>
                <p>Welcome back, {{ Auth::user()->name }}!</p>
            </div>

            <a href="{{ route('visits.create') }}" class="dashboard-add-button">
                <span>+</span>
                <span>Add Student Visit</span>
            </a>
        </div>
    </x-slot>

    <div class="dashboard-page">
        <div class="dashboard-container">
            <div class="dashboard-stat-grid">
                <div class="dashboard-stat-card">
                    <div class="dashboard-stat-icon dashboard-stat-teal">V</div>
                    <div>
                        <p>Visits Today</p>
                        <strong>{{ $todayVisits }}</strong>
                        <span>{{ $dailyVisits->sum('count') }} this week</span>
                    </div>
                </div>

                <div class="dashboard-stat-card">
                    <div class="dashboard-stat-icon dashboard-stat-purple">S</div>
                    <div>
                        <p>New Students</p>
                        <strong>{{ $newStudentsToday }}</strong>
                        <span>{{ $dailyNewStudents->sum('count') }} this week</span>
                    </div>
                </div>

                <div class="dashboard-stat-card">
                    <div class="dashboard-stat-icon dashboard-stat-amber">Rx</div>
                    <div>
                        <p>Medicines Dispensed</p>
                        <strong>{{ $medicinesDispensedToday }}</strong>
                        <span>{{ $dailyMedicinesDispensed->sum('count') }} last 7 days</span>
                    </div>
                </div>

                <div class="dashboard-stat-card">
                    <div class="dashboard-stat-icon dashboard-stat-blue">!</div>
                    <div>
                        <p>Low Stock Items</p>
                        <strong>{{ $lowStockMedicines->count() }}</strong>
                        <a href="{{ route('medicines.index') }}">View inventory</a>
                    </div>
                </div>
            </div>

            <div class="dashboard-two-column">
                <section class="dashboard-panel">
                    <div class="dashboard-panel-header">
                        <h2>Visits Overview <span>(This Week)</span></h2>
                        <span class="dashboard-period-badge">This Week</span>
                    </div>

                    @php
                        $maxVisits = max($dailyVisits->max('count'), 1);
                        $points = $dailyVisits->values()->map(function ($day, $index) use ($maxVisits) {
                            $x = 24 + ($index * 76);
                            $y = 160 - (($day['count'] / $maxVisits) * 118);
                            return "{$x},{$y}";
                        })->implode(' ');
                    @endphp

                    <div class="dashboard-chart">
                        <svg viewBox="0 0 520 205">
                            <line x1="24" y1="42" x2="500" y2="42" stroke="#E5E7EB" stroke-dasharray="4 4" />
                            <line x1="24" y1="82" x2="500" y2="82" stroke="#E5E7EB" stroke-dasharray="4 4" />
                            <line x1="24" y1="122" x2="500" y2="122" stroke="#E5E7EB" stroke-dasharray="4 4" />
                            <line x1="24" y1="160" x2="500" y2="160" stroke="#E5E7EB" />
                            <polyline points="{{ $points }}" fill="none" stroke="#0DD7C9" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
                            @foreach($dailyVisits->values() as $index => $day)
                                @php
                                    $x = 24 + ($index * 76);
                                    $y = 160 - (($day['count'] / $maxVisits) * 118);
                                @endphp
                                <circle cx="{{ $x }}" cy="{{ $y }}" r="5" fill="#0DD7C9" />
                                <text x="{{ $x }}" y="{{ $y - 14 }}" text-anchor="middle" class="chart-value">{{ $day['count'] }}</text>
                                <text x="{{ $x }}" y="190" text-anchor="middle" class="chart-label">{{ $day['label'] }}</text>
                            @endforeach
                        </svg>
                    </div>
                </section>

                <section class="dashboard-panel">
                    <h2 class="dashboard-section-title">Top Complaints <span>(This Week)</span></h2>

                    @php
                        $complaintColors = ['#0DD7C9', '#7C3AED', '#F59E0B', '#0EA5E9', '#D1D5DB'];
                        $complaintTotal = max((int) $topComplaints->sum('count'), 1);
                        $complaintStart = 0;
                        $complaintGradient = $topComplaints->take(5)->values()->map(function ($item, $index) use (&$complaintStart, $complaintTotal, $complaintColors) {
                            $slice = ((int) $item->count / $complaintTotal) * 100;
                            $end = $complaintStart + $slice;
                            $segment = "{$complaintColors[$index]} {$complaintStart}% {$end}%";
                            $complaintStart = $end;

                            return $segment;
                        })->implode(', ');
                        $complaintGradient = $complaintGradient ?: '#E5E7EB 0% 100%';
                    @endphp

                    <div class="complaints-grid">
                        <div class="complaints-chart" style="background: conic-gradient({{ $complaintGradient }});">
                            <div></div>
                        </div>

                        <div class="complaints-list">
                            @forelse($topComplaints->take(5) as $index => $item)
                                @php($percentage = round(((int) $item->count / $complaintTotal) * 100))
                                <div class="complaint-item">
                                    <div>
                                        <div class="complaint-name">
                                            <span style="background-color: {{ $complaintColors[$index] ?? '#D1D5DB' }}"></span>
                                            <p>{{ $item->complaint }}</p>
                                        </div>
                                        <small>{{ $item->student_names }}</small>
                                    </div>
                                    <strong>{{ $item->count }} <span>({{ $percentage }}%)</span></strong>
                                </div>
                            @empty
                                <p class="empty-text">No complaint data yet.</p>
                            @endforelse

                            <a href="{{ route('visits.index') }}" class="complaints-action">
                                View all complaints
                            </a>
                        </div>
                    </div>
                </section>
            </div>

            <div class="dashboard-two-column">
                <section class="dashboard-panel">
                    <div class="dashboard-panel-header">
                        <h2>Recent Visits</h2>
                        <a href="{{ route('visits.index') }}">View all</a>
                    </div>

                    <div class="dashboard-list">
                        @forelse($recentVisits->take(4) as $visit)
                            @php($initials = collect(explode(' ', $visit->student->name ?? 'NA'))->filter()->map(fn ($part) => strtoupper(substr($part, 0, 1)))->take(2)->implode(''))
                            <div class="dashboard-list-item">
                                <div class="dashboard-avatar">{{ $initials ?: 'NA' }}</div>
                                <div class="dashboard-list-main">
                                    <p>{{ $visit->student->name ?? 'Unknown' }}</p>
                                    <span>{{ $visit->complaint }}</span>
                                </div>
                                <time>{{ $visit->visited_at->format('M d, h:i A') }}</time>
                                <span class="dashboard-status dashboard-status-{{ $visit->status }}">
                                    {{ ucfirst($visit->status) }}
                                </span>
                            </div>
                        @empty
                            <p class="empty-text">No visits recorded yet.</p>
                        @endforelse
                    </div>
                </section>

                <section class="dashboard-panel">
                    <div class="dashboard-panel-header">
                        <h2>Low Stock Alerts</h2>
                        <a href="{{ route('medicines.index') }}">View all</a>
                    </div>

                    <div class="stock-list">
                        @forelse($lowStockMedicines->take(3) as $medicine)
                            @php($percent = min(100, max(6, $medicine->quantity * 10)))
                            <div class="stock-item">
                                <div class="stock-icon">!</div>
                                <div class="stock-main">
                                    <div class="stock-row">
                                        <div>
                                            <p>{{ $medicine->name }}</p>
                                            <span>{{ ucfirst($medicine->unit) }}</span>
                                        </div>
                                        <strong>{{ $medicine->quantity }} left</strong>
                                    </div>
                                    <div class="stock-meter">
                                        <div style="width: {{ $percent }}%"></div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="empty-text">No low stock alerts.</p>
                        @endforelse
                    </div>

                    <a href="{{ route('medicines.index') }}" class="dashboard-panel-action">
                        Manage Inventory
                    </a>
                </section>
            </div>
        </div>
    </div>
</x-app-layout>
