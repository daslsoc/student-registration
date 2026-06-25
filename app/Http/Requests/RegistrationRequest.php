<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validation rules shared by the new-registration and update-registration
 * flows. Both posts send the same parent + children payload, so the rules
 * live here once instead of being duplicated in the controller.
 */
class RegistrationRequest extends FormRequest
{
    /**
     * Registration is a public action (new families have no account) and the
     * update flow is gated by a signed, single-use token checked in the
     * controller — so there is no per-user authorization to enforce here.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'parent1_first_name' => 'required|string|min:2|max:50',
            'parent1_last_name' => 'required|string|min:2|max:50',
            'parent1_email' => 'required|email|max:255',
            'parent1_phone' => 'required|string|min:10|max:10',
            'parent2_first_name' => 'nullable|string|min:2|max:50',
            'parent2_last_name' => 'nullable|string|min:2|max:50',
            'parent2_email' => 'nullable|email|max:255',
            'parent2_phone' => 'nullable|string|min:10|max:10',
            'emergency_contact_name' => 'required|string|max:255',
            'emergency_contact_phone' => 'required|string|min:10|max:10',
            'relationship_to_family' => 'required|string|max:255',
            'postcode' => 'nullable|integer|min:4',
            'guidelines_accepted' => 'required|accepted',
            'children' => 'required|array|min:1',
            'children.*.id' => 'nullable|integer|exists:children,id',
            'children.*.first_name' => 'required|string|max:255',
            'children.*.last_name' => 'required|string|max:255',
            'children.*.gender' => 'required|string|in:Male,Female',
            'children.*.date_of_birth' => [
                'required',
                'date_format:Y-m-d',
                'before:'.Carbon::now()->subYears(config('custom.school.minimum_child_age'))->format('Y-m-d'),
            ],
            'children.*.residency_status' => 'required|string|in:Temporary Resident,Permanent Resident,Citizen',
            'children.*.day_school_name' => 'required|string|max:255',
            'children.*.day_school_year' => 'required|string|in:Pre School,Kindergarten,Grade 1,Grade 2,Grade 3,Grade 4,Grade 5,Grade 6,Grade 7,Grade 8,Grade 9,Grade 10,Grade 11,Grade 12',
            'children.*.allergies' => 'nullable|string',
            'children.*.special_needs' => 'nullable|string',
            'children.*.dhamma_class' => 'required|string|in:Did not attend last year,Class 1 (A),Class 1 (B),Class 2 (C),Class 3 (D),Class 4 (E)',
            'children.*.sinhala_class' => 'required|string|in:Did not attend last year,Class 1 (A),Class 1 (B),Class 2 (C),Class 3 (D),Class 4 (E)',
            'children.*.year_of_first_registration' => 'nullable|integer|min:1991|max:'.now()->year,
            'children.*.photography_allowed' => 'accepted|sometimes',
        ];
    }
}
