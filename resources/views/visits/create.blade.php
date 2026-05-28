<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Record New Visit</h2>
    </x-slot>

    <div class="py-8 max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow rounded-lg p-6">
            <form method="POST" action="{{ route('visits.store') }}">
                @csrf

                <div class="grid grid-cols-1 gap-4">

                    {{-- Student --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Student <span class="text-red-500">*</span></label>
                        <select name="student_id" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary @error('student_id') border-red-400 @enderror">
                            <option value="">-- Select Student --</option>
                            @foreach($students as $student)
                                <option value="{{ $student->id }}" {{ old('student_id') == $student->id ? 'selected' : '' }}>
                                    {{ $student->name }} ({{ $student->student_id }}) — {{ $student->grade_level }} {{ $student->section }}
                                </option>
                            @endforeach
                        </select>
                        @error('student_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Complaint --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Complaint <span class="text-red-500">*</span></label>
                        <textarea name="complaint" rows="3"
                                  class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary @error('complaint') border-red-400 @enderror"
                                  placeholder="Describe the student's complaint...">{{ old('complaint') }}</textarea>
                        @error('complaint') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Diagnosis --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Diagnosis</label>
                        <textarea name="diagnosis" rows="2"
                                  class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary"
                                  placeholder="Nurse's diagnosis (optional)">{{ old('diagnosis') }}</textarea>
                    </div>

                    {{-- Treatment --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Treatment</label>
                        <textarea name="treatment" rows="2"
                                  class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary"
                                  placeholder="Treatment given (optional)">{{ old('treatment') }}</textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        {{-- Status --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status <span class="text-red-500">*</span></label>
                            <select name="status" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary @error('status') border-red-400 @enderror">
                                <option value="pending"  {{ old('status', 'pending') === 'pending'  ? 'selected' : '' }}>Pending</option>
                                <option value="treated"  {{ old('status') === 'treated'  ? 'selected' : '' }}>Treated</option>
                                <option value="referred" {{ old('status') === 'referred' ? 'selected' : '' }}>Referred</option>
                            </select>
                            @error('status') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- Visited At --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date & Time <span class="text-red-500">*</span></label>
                            <input type="datetime-local" name="visited_at"
                                   value="{{ old('visited_at', now()->format('Y-m-d\TH:i')) }}"
                                   class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary @error('visited_at') border-red-400 @enderror">
                            @error('visited_at') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- Medicines diri (optional) --}}
                    @if($medicines->isNotEmpty())
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Medicines Dispensed <span class="text-gray-400 font-normal text-xs">(optional)</span></label>
                        <div class="border border-gray-200 rounded p-3 grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-48 overflow-y-auto">
                            @foreach($medicines as $medicine)
                            <div class="flex items-center gap-2">
                                <span class="text-sm text-gray-700 flex-1">{{ $medicine->name }} <span class="text-xs text-gray-400">({{ $medicine->quantity }} {{ $medicine->unit }}s left)</span></span>
                                <input type="number" name="medicines[{{ $medicine->id }}]"
                                       min="0" max="{{ $medicine->quantity }}" value="0"
                                       class="w-16 border border-gray-300 rounded px-2 py-1 text-xs text-center focus:outline-none focus:ring-1 focus:ring-primary">
                            </div>
                            @endforeach
                        </div>
                        <p class="text-xs text-gray-400 mt-1">Enter quantity given. Leave 0 if not dispensed.</p>
                    </div>
                    @endif

                </div>

                <div class="mt-6 flex gap-3">
                    <button type="submit" class="bg-primary text-gray-900 px-5 py-2 rounded hover:bg-primary-dark text-sm font-medium">Record Visit</button>
                    <a href="{{ route('visits.index') }}" class="bg-gray-200 text-gray-700 px-5 py-2 rounded hover:bg-gray-300 text-sm">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>


