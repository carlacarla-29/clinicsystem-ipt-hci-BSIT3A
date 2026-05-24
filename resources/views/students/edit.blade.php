<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Student</h2>
    </x-slot>

    <div class="py-8 max-w-2xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow rounded-lg p-6">
            <form method="POST" action="{{ route('students.update', $student) }}">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Student ID <span class="text-red-500">*</span></label>
                        <input type="text" name="student_id" value="{{ old('student_id', $student->student_id) }}"
                               class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary @error('student_id') border-red-400 @enderror">
                        @error('student_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Full Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $student->name) }}"
                               class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary @error('name') border-red-400 @enderror">
                        @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Grade Level <span class="text-red-500">*</span></label>
                            <input type="text" name="grade_level" value="{{ old('grade_level', $student->grade_level) }}"
                                   class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary @error('grade_level') border-red-400 @enderror">
                            @error('grade_level') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Section <span class="text-red-500">*</span></label>
                            <input type="text" name="section" value="{{ old('section', $student->section) }}"
                                   class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary @error('section') border-red-400 @enderror">
                            @error('section') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Gender <span class="text-red-500">*</span></label>
                            <select name="gender" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary @error('gender') border-red-400 @enderror">
                                <option value="">-- Select --</option>
                                <option value="male" {{ old('gender', $student->gender) === 'male' ? 'selected' : '' }}>Male</option>
                                <option value="female" {{ old('gender', $student->gender) === 'female' ? 'selected' : '' }}>Female</option>
                            </select>
                            @error('gender') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Birthdate</label>
                            <input type="date" name="birthdate" value="{{ old('birthdate', $student->birthdate?->format('Y-m-d')) }}"
                                   class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary @error('birthdate') border-red-400 @enderror">
                            @error('birthdate') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex gap-3">
                    <button type="submit" class="bg-primary text-gray-900 px-5 py-2 rounded hover:bg-primary-dark text-sm font-medium">Save Changes</button>
                    <a href="{{ route('students.show', $student) }}" class="bg-gray-200 text-gray-700 px-5 py-2 rounded hover:bg-gray-300 text-sm">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>


