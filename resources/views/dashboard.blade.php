<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900">Dashboard</h1>
                <p class="mt-1 text-sm text-gray-500">Welcome back, {{ Auth::user()->name }}!</p>
            </div>

            <a href="{{ route('visits.create') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-primary px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-primary-dark">
                <span class="text-lg leading-none">+</span>
                <span>Add Student Visit</span>
            </a>
        </div>
    </x-slot>

    <div class="pb-8">
        <div class="mx-auto max-w-7xl space-y-5 px-4 sm:px-6 lg:px-10">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center gap-4">
                        <div class="flex h-16 w-16 items-center justify-center rounded-xl bg-primary-light text-3xl text-primary-dark">♧</div>
                        <div>
                            <p class="text-sm text-gray-500">Visits Today</p>
                            <p class="mt-2 text-3xl font-bold text-gray-900">{{ $todayVisits }}</p>
                            <p class="mt-2 text-xs text-gray-500"><span class="font-semibold text-primary-dark">↑</span> {{ $dailyVisits->sum('count') }} this week</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center gap-4">
                        <div class="flex h-16 w-16 items-center justify-center rounded-xl bg-violet-50 text-3xl text-violet-600">▾</div>
                        <div>
                            <p class="text-sm text-gray-500">New Students</p>
                            <p class="mt-2 text-3xl font-bold text-gray-900">{{ $newStudentsToday }}</p>
                            <p class="mt-2 text-xs text-gray-500"><span class="font-semibold text-primary-dark">↑</span> {{ $dailyNewStudents->sum('count') }} this week</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center gap-4">
                        <div class="flex h-16 w-16 items-center justify-center rounded-xl bg-amber-50 text-3xl text-amber-600">▣</div>
                        <div>
                            <p class="text-sm text-gray-500">Medicines Dispensed</p>
                            <p class="mt-2 text-3xl font-bold text-gray-900">{{ $medicinesDispensedToday }}</p>
                            <p class="mt-2 text-xs text-gray-500"><span class="font-semibold text-primary-dark">↑</span> {{ $dailyMedicinesDispensed->sum('count') }} last 7 days</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center gap-4">
                        <div class="flex h-16 w-16 items-center justify-center rounded-xl bg-sky-50 text-3xl text-sky-600">□</div>
                        <div>
                            <p class="text-sm text-gray-500">Low Stock Items</p>
                            <p class="mt-2 text-3xl font-bold text-gray-900">{{ $lowStockMedicines->count() }}</p>
                            <a href="{{ route('medicines.index') }}" class="mt-2 inline-flex text-xs font-semibold text-primary-dark hover:underline">View inventory →</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-5 xl:grid-cols-2">
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <div class="mb-5 flex items-center justify-between">
                        <h2 class="text-base font-semibold text-gray-900">Visits Overview <span class="font-normal text-gray-500">(This Week)</span></h2>
                        <span class="rounded-lg border border-gray-200 px-3 py-2 text-xs font-medium text-gray-600">This Week</span>
                    </div>

                    @php
                        $maxVisits = max($dailyVisits->max('count'), 1);
                        $points = $dailyVisits->values()->map(function ($day, $index) use ($maxVisits) {
                            $x = 24 + ($index * 76);
                            $y = 160 - (($day['count'] / $maxVisits) * 118);
                            return "{$x},{$y}";
                        })->implode(' ');
                    @endphp

                    <div class="h-64">
                        <svg viewBox="0 0 520 205" class="h-full w-full overflow-visible">
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
                                <text x="{{ $x }}" y="{{ $y - 14 }}" text-anchor="middle" class="fill-gray-900 text-[13px] font-semibold">{{ $day['count'] }}</text>
                                <text x="{{ $x }}" y="190" text-anchor="middle" class="fill-gray-500 text-[12px]">{{ $day['label'] }}</text>
                            @endforeach
                        </svg>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <h2 class="mb-5 text-base font-semibold text-gray-900">Top Complaints <span class="font-normal text-gray-500">(This Week)</span></h2>
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
                        <div class="mx-auto h-44 w-44 rounded-full p-8" style="background: conic-gradient({{ $complaintGradient }});">
                            <div class="h-full w-full rounded-full bg-white"></div>
                        </div>
                        <div class="min-w-0 space-y-3">
                            @forelse($topComplaints->take(5) as $index => $item)
                                @php($percentage = round(((int) $item->count / $complaintTotal) * 100))
                                <div class="flex min-w-0 items-start justify-between gap-4">
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-2">
                                            <span class="h-2.5 w-2.5 rounded-full" style="background-color: {{ $complaintColors[$index] ?? '#D1D5DB' }}"></span>
                                            <p class="min-w-0 truncate text-sm font-medium text-gray-700">{{ $item->complaint }}</p>
                                        </div>
                                        <p class="mt-1 truncate pl-5 text-xs text-gray-500">{{ $item->student_names }}</p>
                                    </div>
                                    <span class="shrink-0 text-sm font-semibold text-gray-900">{{ $item->count }} <span class="font-normal text-gray-500">({{ $percentage }}%)</span></span>
                                </div>
                            @empty
                                <p class="text-sm text-gray-400">No complaint data yet.</p>
                            @endforelse
                            <a href="{{ route('visits.index') }}" class="complaints-action mt-4 inline-flex w-full justify-center rounded-lg border border-primary/40 px-4 py-2 text-sm font-semibold text-primary-dark hover:bg-primary-light">
                                View all complaints
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-5 xl:grid-cols-2">
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="text-base font-semibold text-gray-900">Recent Visits</h2>
                        <a href="{{ route('visits.index') }}" class="text-xs font-semibold text-primary-dark hover:underline">View all</a>
                    </div>
                    <div class="divide-y divide-gray-100">
                        @forelse($recentVisits->take(4) as $visit)
                            @php($initials = collect(explode(' ', $visit->student->name ?? 'NA'))->filter()->map(fn ($part) => strtoupper(substr($part, 0, 1)))->take(2)->implode(''))
                            <div class="flex items-center gap-4 py-3">
                                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-primary-light text-sm font-bold text-primary-dark">{{ $initials ?: 'NA' }}</div>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-semibold text-gray-900">{{ $visit->student->name ?? 'Unknown' }}</p>
                                    <p class="truncate text-xs text-gray-500">{{ $visit->complaint }}</p>
                                </div>
                                <p class="hidden shrink-0 text-xs text-gray-500 sm:block">{{ $visit->visited_at->format('M d, h:i A') }}</p>
                                <span class="shrink-0 rounded-lg px-3 py-1 text-xs font-semibold
                                    @if($visit->status === 'treated') bg-green-50 text-green-700
                                    @elseif($visit->status === 'referred') bg-red-50 text-red-700
                                    @else bg-amber-50 text-amber-700 @endif">
                                    {{ ucfirst($visit->status) }}
                                </span>
                            </div>
                        @empty
                            <p class="py-4 text-sm text-gray-400">No visits recorded yet.</p>
                        @endforelse
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="text-base font-semibold text-gray-900">Low Stock Alerts</h2>
                        <a href="{{ route('medicines.index') }}" class="text-xs font-semibold text-primary-dark hover:underline">View all</a>
                    </div>
                    <div class="space-y-4">
                        @forelse($lowStockMedicines->take(3) as $medicine)
                            @php($percent = min(100, max(6, $medicine->quantity * 10)))
                            <div class="flex items-center gap-4">
                                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-red-50 text-red-500">◇</div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center justify-between gap-3">
                                        <div>
                                            <p class="truncate text-sm font-semibold text-gray-900">{{ $medicine->name }}</p>
                                            <p class="text-xs text-gray-500">{{ ucfirst($medicine->unit) }}</p>
                                        </div>
                                        <span class="shrink-0 text-xs font-bold text-red-500">{{ $medicine->quantity }} left</span>
                                    </div>
                                    <div class="mt-2 h-1.5 rounded-full bg-gray-200">
                                        <div class="h-1.5 rounded-full bg-red-500" style="width: {{ $percent }}%"></div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-400">No low stock alerts.</p>
                        @endforelse
                    </div>
                    <a href="{{ route('medicines.index') }}" class="mt-5 inline-flex w-full justify-center rounded-lg border border-primary/40 px-4 py-2 text-sm font-semibold text-primary-dark hover:bg-primary-light">
                        Manage Inventory
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
