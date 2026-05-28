<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVisitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = (int) $this->user()->id;

        return [
            'student_id' => [
                'required',
                Rule::exists('students', 'id')->where(fn ($query) =>
                    $query->where('user_id', $userId)
                ),
            ],

            'complaint'  => ['required', 'string', 'min:5'],

            'diagnosis'  => ['nullable', 'string'],
            'treatment'  => ['nullable', 'string'],

            'status'     => ['required', 'in:pending,treated,referred'],

            'visited_at' => ['required', 'date'],
            'medicines' => ['nullable', 'array'],
            'medicines.*' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'student_id.required' => 'Please select a student.',
            'student_id.exists'   => 'The selected student does not exist.',
            'complaint.required'  => 'Please describe the student\'s complaint.',
            'complaint.min'       => 'The complaint must be at least 5 characters.',
            'status.required'     => 'Please set the visit status.',
            'status.in'           => 'Status must be: pending, treated, or referred.',
            'visited_at.required' => 'Please provide the date and time of the visit.',
            'visited_at.date'     => 'The visit date is not a valid date.',
        ];
    }
}
