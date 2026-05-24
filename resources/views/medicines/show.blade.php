<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $medicine->name }}</h2>
                <p class="text-sm text-gray-500">{{ ucfirst($medicine->unit) }} · {{ $medicine->quantity }} in stock</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('medicines.edit', $medicine) }}" class="bg-primary text-gray-900 px-4 py-2 rounded hover:bg-primary-dark text-sm">Edit</a>
                <a href="{{ route('medicines.index') }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded hover:bg-gray-300 text-sm">Back</a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div>
                    <h3 class="text-sm font-semibold text-gray-500">Medicine</h3>
                    <p class="mt-1 text-gray-900">{{ $medicine->name }}</p>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-500">Unit</h3>
                    <p class="mt-1 text-gray-900">{{ ucfirst($medicine->unit) }}</p>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-500">Quantity</h3>
                    <p class="mt-1 text-gray-900">{{ $medicine->quantity }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Dispensed in Visits</h3>

            @if($medicine->visits->isEmpty())
                <p class="text-sm text-gray-500">This medicine has not been dispensed in any recorded visit yet.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Visit ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity Given</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($medicine->visits as $visit)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">#{{ $visit->id }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $visit->student->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $visit->pivot->quantity_given }} {{ $medicine->unit }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $visit->visited_at?->format('M d, Y') ?? 'N/A' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>


