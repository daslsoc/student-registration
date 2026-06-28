<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Child;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * The integration delta endpoint the attendance app syncs from.
 *
 * Returns the paid children (those whose parent has a payment with a paid_date)
 * and their allocated class per subject. `last_changed_at` is the high-water
 * mark — the newest updated_at across all paid children — so the consumer can
 * store it and pass it back as `?since=` to fetch only what changed. With no
 * `since`, it returns everyone (a full sync).
 *
 * Only the fields needed to create a student and their enrollments are exposed
 * — no parent, contact, or date-of-birth data.
 */
class ChangesController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $since = $request->query('since');

        $paid = fn () => Child::query()
            ->whereNotNull('student_number')
            ->whereHas('parent.payments', fn ($q) => $q->whereNotNull('paid_date'));

        $lastChangedAt = $paid()->max('updated_at');
        $count = $paid()->count();

        $students = $paid()
            ->when($since, fn ($q) => $q->where('updated_at', '>=', $since))
            ->orderBy('student_number')
            ->get(['student_number', 'first_name', 'last_name', 'allocated_dhamma_class', 'allocated_sinhala_class'])
            ->map(fn (Child $child) => [
                'student_number' => (string) $child->student_number,
                'first_name' => $child->first_name,
                'last_name' => $child->last_name,
                'allocated_dhamma_class' => $child->allocated_dhamma_class,
                'allocated_sinhala_class' => $child->allocated_sinhala_class,
            ]);

        return response()->json([
            'last_changed_at' => $lastChangedAt ? (string) $lastChangedAt : null,
            'count' => $count,
            'students' => $students,
        ]);
    }
}
