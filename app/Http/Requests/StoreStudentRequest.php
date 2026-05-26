<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStudentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Must return TRUE so authenticated users can submit the form.
     */
    public function authorize(): bool
    {
        return true; // FIX: was 'false' — this caused 403 Forbidden on every submit
    }

    /**
     * Validation rules for creating OR updating a student.
     *
     * For update: the 'student_id' unique rule ignores the current record
     * so editing a student doesn't fail with a false "duplicate" error.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // When updating ($this->student is route-model-bound), ignore the current student's row.
        // When creating, $this->student is null so no row is ignored.
        $student = $this->route('student');
        $studentId = $student?->id;
        $userId = (int) $this->user()->id;

        return [
            'student_id'  => [
                'required',
                'string',
                'max:20',
                Rule::unique('students', 'student_id')
                    ->where(fn ($query) => $query->where('user_id', $userId))
                    ->ignore($studentId),
            ],
            'name'        => ['required', 'string', 'max:100'],
            'grade_level' => ['required', 'string', 'max:50'],
            'section'     => ['required', 'string', 'max:50'],
            'gender'      => ['required', 'in:male,female'],
            'birthdate'   => ['nullable', 'date', 'before:today'],
        ];
    }

    /**
     * Human-readable error messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'student_id.required' => 'Student ID is required.',
            'student_id.unique'   => 'This Student ID is already registered.',
            'name.required'       => 'Student name is required.',
            'grade_level.required'=> 'Grade level is required.',
            'section.required'    => 'Section is required.',
            'gender.required'     => 'Gender is required.',
            'gender.in'           => 'Gender must be male or female.',
            'birthdate.before'    => 'Birthdate must be a past date.',
        ];
    }
}
