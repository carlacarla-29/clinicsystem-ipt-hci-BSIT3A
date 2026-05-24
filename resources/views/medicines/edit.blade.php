<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Medicine</h2>
    </x-slot>

    <div class="py-8 max-w-2xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow rounded-lg p-6">
            <form method="POST" action="{{ route('medicines.update', $medicine) }}">
                @csrf
                @method('PUT')

                <div class="grid gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Medicine Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $medicine->name) }}" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary @error('name') border-red-400 @enderror">
                        @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Unit <span class="text-red-500">*</span></label>
                        <select name="unit" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary @error('unit') border-red-400 @enderror">
                            <option value="">-- Select unit --</option>
                            <option value="tablet" {{ old('unit', $medicine->unit) === 'tablet' ? 'selected' : '' }}>Tablet</option>
                            <option value="capsule" {{ old('unit', $medicine->unit) === 'capsule' ? 'selected' : '' }}>Capsule</option>
                            <option value="ml" {{ old('unit', $medicine->unit) === 'ml' ? 'selected' : '' }}>ml</option>
                            <option value="sachet" {{ old('unit', $medicine->unit) === 'sachet' ? 'selected' : '' }}>Sachet</option>
                            <option value="piece" {{ old('unit', $medicine->unit) === 'piece' ? 'selected' : '' }}>Piece</option>
                        </select>
                        @error('unit') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Quantity <span class="text-red-500">*</span></label>
                        <input type="number" name="quantity" value="{{ old('quantity', $medicine->quantity) }}" min="0" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary @error('quantity') border-red-400 @enderror">
                        @error('quantity') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="mt-6 flex gap-3">
                    <button type="submit" class="bg-primary text-gray-900 px-5 py-2 rounded hover:bg-primary-dark text-sm font-medium">Update Medicine</button>
                    <a href="{{ route('medicines.index') }}" class="bg-gray-200 text-gray-700 px-5 py-2 rounded hover:bg-gray-300 text-sm">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>


