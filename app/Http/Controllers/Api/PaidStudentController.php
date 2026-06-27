<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Child;
use Illuminate\Http\JsonResponse;

/**
 * Read-only integration endpoint: the children whose family has paid.
 *
 * "Paid" means the child's parent has at least one payment with a paid_date
 * set (written together with registration_status = completed at payment time).
 * Only the fields the attendance app needs to create a student, map enrollments,
 * and let a teacher decide a class (name, date of birth, day school) are
 * returned — no parent details, no contact info.
 */
class PaidStudentController extends Controller
{
    public function index(): JsonResponse
    {
        $children = Child::query()
            ->whereNotNull('student_number')
            ->whereHas('parent.payments', function ($query) {
                $query->whereNotNull('paid_date');
            })
            ->orderBy('student_number')
            ->get([
                'student_number', 'first_name', 'last_name',
                'date_of_birth', 'day_school_name', 'day_school_year',
                'dhamma_class', 'sinhala_class',
            ])
            ->map(fn (Child $child) => [
                'student_number' => (string) $child->student_number,
                'first_name' => $child->first_name,
                'last_name' => $child->last_name,
                'date_of_birth' => $child->date_of_birth ? (string) $child->date_of_birth : null,
                'day_school_name' => $child->day_school_name,
                'day_school_year' => $child->day_school_year !== null ? (string) $child->day_school_year : null,
                'dhamma_class' => $child->dhamma_class,
                'sinhala_class' => $child->sinhala_class,
            ]);

        return response()->json(['data' => $children]);
    }
}
