<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Medicine Inventory</h2>
            <a href="{{ route('medicines.create') }}" class="inline-flex items-center justify-center bg-primary text-gray-900 px-4 py-2 rounded hover:bg-primary-dark text-sm font-semibold">
                Add New Medicine
            </a>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8">
        @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded">
                {{ session('success') }}
            </div>
        @endif

        @if($lowStock->isNotEmpty())
            <div class="mb-6 bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded">
                <h3 class="font-semibold">⚠ Low Stock Medicines</h3>
                <ul class="mt-2 list-disc list-inside text-sm">
                    @foreach($lowStock as $medicine)
                        <li>
                            {{ $medicine->name }} — {{ $medicine->quantity }} {{ $medicine->unit }} left
                            <a href="{{ route('medicines.edit', $medicine) }}" class="underline text-primary hover:text-primary-dark ms-2">Restock</a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($medicines as $medicine)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $medicine->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ ucfirst($medicine->unit) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $medicine->quantity }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('medicines.show', $medicine) }}" class="text-primary hover:text-primary-dark">View</a>
                                        <a href="{{ route('medicines.edit', $medicine) }}" class="text-primary-dark hover:text-primary">Edit</a>
                                        <form method="POST" action="{{ route('medicines.destroy', $medicine) }}" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800" onclick="return confirm('Remove this medicine from inventory?')">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-10 text-center text-sm text-gray-500">No medicines found. Add one to begin tracking inventory.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>



