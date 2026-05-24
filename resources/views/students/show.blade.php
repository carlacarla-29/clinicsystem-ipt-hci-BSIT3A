<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $student->name }}</h2>
            <div class="flex gap-2">
                <a href="{{ route('students.edit', $student) }}" class="bg-primary text-gray-900 px-4 py-2 rounded hover:bg-primary-dark text-sm">Edit</a>
                <a href="{{ route('visits.create') }}?student_id={{ $student->id }}" class="bg-primary text-gray-900 px-4 py-2 rounded hover:bg-primary-dark text-sm">+ Record Visit</a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

        @if(session('success'))
            <div class="bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded">{{ session('success') }}</div>
        @endif

        {{-- Student Info Card --}}
        <div class="bg-white shadow rounded-lg p-6 grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
            <div><span class="text-gray-500">Student ID:</span> <span class="font-mono font-semibold">{{ $student->student_id }}</span></div>
            <div><span class="text-gray-500">Grade Level:</span> {{ $student->grade_level }}</div>
            <div><span class="text-gray-500">Section:</span> {{ $student->section }}</div>
            <div><span class="text-gray-500">Gender:</span> {{ ucfirst($student->gender) }}</div>
            <div><span class="text-gray-500">Birthdate:</span> {{ $student->birthdate ? $student->birthdate->format('F d, Y') : 'N/A' }}</div>
            <div><span class="text-gray-500">Age:</span> {{ $student->birthdate ? $student->birthdate->age . ' years old' : 'N/A' }}</div>
        </div>

        {{-- Visit History --}}
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 font-semibold text-gray-800">Visit History ({{ $visits->total() }})</div>
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-500">Date & Time</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500">Complaint</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500">Status</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($visits as $visit)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-gray-600">{{ $visit->visited_at->format('M d, Y h:i A') }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ Str::limit($visit->complaint, 60) }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold
                                @if($visit->status === 'treated') bg-green-100 text-green-700
                                @elseif($visit->status === 'referred') bg-red-100 text-red-700
                                @else bg-yellow-100 text-yellow-700 @endif">
                                {{ ucfirst($visit->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <a href="{{ route('visits.show', $visit) }}" class="text-primary hover:underline">View</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-gray-400">No visits recorded yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            @if($visits->hasPages())
                <div class="px-4 py-3">{{ $visits->links() }}</div>
            @endif
        </div>

        <div>
            <a href="{{ route('students.index') }}" class="text-primary hover:underline text-sm">← Back to Students</a>
        </div>
    </div>
</x-app-layout>


