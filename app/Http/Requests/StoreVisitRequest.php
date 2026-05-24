<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * ============================================================
 *  StoreVisitRequest — Form Validation for Creating a Visit
 * ============================================================
 *
 * GUIDE: What is a FormRequest?
 * ------------------------------
 * A FormRequest is a dedicated class that handles validation BEFORE
 * the data ever reaches your controller. It keeps controllers clean.
 *
 * HOW IT WORKS:
 *   1. User submits the create-visit form (POST /visits)
 *   2. Laravel automatically runs this class BEFORE VisitController@store
 *   3. If authorize() returns false → 403 Forbidden
 *   4. If rules() fail → Laravel redirects back with $errors (auto-available in Blade)
 *   5. If everything passes → controller receives clean, validated data
 *
 * IN YOUR CONTROLLER, use it like this:
 *   public function store(StoreVisitRequest $request)
 *   {
 *       $validated = $request->validated(); // only the validated fields
 *   }
 *
 * IN YOUR BLADE, display errors like this:
 *   @error('complaint')
 *       <span class="text-red-500">{{ $message }}</span>
 *   @enderror
 * ============================================================
 */
class StoreVisitRequest extends FormRequest
{
    /**
     * Authorize this request.
     *
     * Return true → any authenticated user can submit visits.
     * You could add role-based logic here later, e.g.:
     *   return auth()->user()->hasRole('nurse');
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules applied to the incoming request data.
     *
     * RULE REFERENCE:
     *   required        → field must be present and not empty
     *   nullable        → field is optional (allows null)
     *   string          → must be a string value
     *   min:5           → minimum 5 characters
     *   exists:table,col → value must exist in the given table/column
     *   in:a,b,c        → value must be one of these exact options
     *   date            → must be a valid date string
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // student_id must match a real record in the students table
            'student_id' => ['required', 'exists:students,id'],

            // Complaint is required and must be meaningful (min 5 chars)
            'complaint'  => ['required', 'string', 'min:5'],

            // Diagnosis and treatment are filled by the nurse, may be left blank initially
            'diagnosis'  => ['nullable', 'string'],
            'treatment'  => ['nullable', 'string'],

            // Status must be one of exactly these three values
            'status'     => ['required', 'in:pending,treated,referred'],

            // Visit time — defaults to now() in the controller if not provided
            'visited_at' => ['required', 'date'],
        ];
    }

    /**
     * Custom human-readable error messages (optional but user-friendly).
     *
     * Without this, Laravel shows generic messages like "The student id field is required."
     * With this, you control exactly what the user sees.
     *
     * @return array<string, string>
     */
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
