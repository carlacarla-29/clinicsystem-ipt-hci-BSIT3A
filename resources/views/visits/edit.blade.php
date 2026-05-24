<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Visit</h2>
    </x-slot>

    <div class="py-8 max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow rounded-lg p-6">
            <form method="POST" action="{{ route('visits.update', $visit) }}">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 gap-4">

                    {{-- Student --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Student <span class="text-red-500">*</span></label>
                        <select name="student_id" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary @error('student_id') border-red-400 @enderror">
                            <option value="">-- Select Student --</option>
                            @foreach($students as $student)
                                <option value="{{ $student->id }}" {{ old('student_id', $visit->student_id) == $student->id ? 'selected' : '' }}>
                                    {{ $student->name }} ({{ $student->student_id }})
                                </option>
                            @endforeach
                        </select>
                        @error('student_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Complaint --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Complaint <span class="text-red-500">*</span></label>
                        <textarea name="complaint" rows="3"
                                  class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary @error('complaint') border-red-400 @enderror">{{ old('complaint', $visit->complaint) }}</textarea>
                        @error('complaint') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Diagnosis --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Diagnosis</label>
                        <textarea name="diagnosis" rows="2"
                                  class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">{{ old('diagnosis', $visit->diagnosis) }}</textarea>
                    </div>

                    {{-- Treatment --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Treatment</label>
                        <textarea name="treatment" rows="2"
                                  class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">{{ old('treatment', $visit->treatment) }}</textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        {{-- Status --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status <span class="text-red-500">*</span></label>
                            <select name="status" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary @error('status') border-red-400 @enderror">
                                <option value="pending"  {{ old('status', $visit->status) === 'pending'  ? 'selected' : '' }}>Pending</option>
                                <option value="treated"  {{ old('status', $visit->status) === 'treated'  ? 'selected' : '' }}>Treated</option>
                                <option value="referred" {{ old('status', $visit->status) === 'referred' ? 'selected' : '' }}>Referred</option>
                            </select>
                            @error('status') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- Visited At --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date & Time <span class="text-red-500">*</span></label>
                            <input type="datetime-local" name="visited_at"
                                   value="{{ old('visited_at', $visit->visited_at->format('Y-m-d\TH:i')) }}"
                                   class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary @error('visited_at') border-red-400 @enderror">
                            @error('visited_at') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                </div>

                <div class="mt-6 flex gap-3">
                    <button type="submit" class="bg-primary text-gray-900 px-5 py-2 rounded hover:bg-primary-dark text-sm font-medium">Save Changes</button>
                    <a href="{{ route('visits.show', $visit) }}" class="bg-gray-200 text-gray-700 px-5 py-2 rounded hover:bg-gray-300 text-sm">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>


