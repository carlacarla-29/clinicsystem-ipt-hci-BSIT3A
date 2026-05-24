<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Visit Details</h2>
            <div class="flex gap-2">
                <a href="{{ route('visits.edit', $visit) }}" class="bg-primary text-gray-900 px-4 py-2 rounded hover:bg-primary-dark text-sm">Edit</a>
                <a href="{{ route('visits.index') }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded hover:bg-gray-300 text-sm">← Back</a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-4">

        {{-- Student Info --}}
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="font-semibold text-gray-700 mb-3 border-b pb-2">Student Information</h3>
            <dl class="grid grid-cols-2 gap-3 text-sm">
                <div><dt class="text-gray-400">Name</dt><dd class="font-medium text-gray-800">{{ $visit->student->name ?? 'N/A' }}</dd></div>
                <div><dt class="text-gray-400">Student ID</dt><dd class="font-mono text-gray-600">{{ $visit->student->student_id ?? 'N/A' }}</dd></div>
                <div><dt class="text-gray-400">Grade & Section</dt><dd class="text-gray-600">{{ ($visit->student->grade_level ?? '') }} — {{ ($visit->student->section ?? '') }}</dd></div>
                <div><dt class="text-gray-400">Gender</dt><dd class="text-gray-600 capitalize">{{ $visit->student->gender ?? 'N/A' }}</dd></div>
            </dl>
        </div>

        {{-- Visit Info --}}
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="font-semibold text-gray-700 mb-3 border-b pb-2">Visit Record</h3>
            <dl class="grid grid-cols-2 gap-3 text-sm">
                <div>
                    <dt class="text-gray-400">Date & Time</dt>
                    <dd class="text-gray-600">{{ $visit->visited_at->format('F d, Y — h:i A') }}</dd>
                </div>
                <div>
                    <dt class="text-gray-400">Status</dt>
                    <dd>
                        <span class="inline-block text-xs px-2 py-0.5 rounded-full
                            @if($visit->status === 'treated') bg-green-100 text-green-700
                            @elseif($visit->status === 'referred') bg-red-100 text-red-700
                            @else bg-yellow-100 text-yellow-700 @endif">
                            {{ ucfirst($visit->status) }}
                        </span>
                    </dd>
                </div>
                <div class="col-span-2">
                    <dt class="text-gray-400">Complaint</dt>
                    <dd class="text-gray-800 mt-1">{{ $visit->complaint }}</dd>
                </div>
                <div class="col-span-2">
                    <dt class="text-gray-400">Diagnosis</dt>
                    <dd class="text-gray-800 mt-1">{{ $visit->diagnosis ?: '—' }}</dd>
                </div>
                <div class="col-span-2">
                    <dt class="text-gray-400">Treatment</dt>
                    <dd class="text-gray-800 mt-1">{{ $visit->treatment ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-400">Recorded By</dt>
                    <dd class="text-gray-600">{{ $visit->recorder->name ?? 'N/A' }}</dd>
                </div>
            </dl>
        </div>

        {{-- Medicines Dispensed --}}
        @if($visit->medicines->isNotEmpty())
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="font-semibold text-gray-700 mb-3 border-b pb-2">Medicines Dispensed</h3>
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500">
                        <th class="py-1 pr-4">Medicine</th>
                        <th class="py-1 pr-4">Unit</th>
                        <th class="py-1">Qty Given</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($visit->medicines as $med)
                    <tr>
                        <td class="py-2 pr-4 text-gray-800">{{ $med->name }}</td>
                        <td class="py-2 pr-4 text-gray-500">{{ $med->unit }}</td>
                        <td class="py-2 font-semibold text-primary">{{ $med->pivot->quantity_given }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        {{-- Delete --}}
        <div class="flex justify-end">
            <form method="POST" action="{{ route('visits.destroy', $visit) }}" onsubmit="return confirm('Permanently delete this visit record?')">
                @csrf @method('DELETE')
                <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 text-sm">Delete Visit</button>
            </form>
        </div>

    </div>
</x-app-layout>


