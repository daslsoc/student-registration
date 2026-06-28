<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Child;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * The integration delta endpoint the attendance app syncs from.
 *
 * Returns two sets:
 *  - `students` — the paid children (parent has a payment with a paid_date) and
 *    their allocated class per subject. The consumer upserts these.
 *  - `removed`  — student_numbers that are NO LONGER in the paid roster (e.g. a
 *    payment was reverted). The consumer deletes these. Deletes are idempotent,
 *    so a student that was never enrolled is harmless to report.
 *
 * `last_changed_at` is the high-water mark — the newest updated_at across every
 * known student (paid or not) — so a removal also advances the consumer's clock.
 * Pass it back as `?since=` to fetch only what changed since; with no `since`,
 * it returns the full roster and the full removed set.
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

        $unpaid = fn () => Child::query()
            ->whereNotNull('student_number')
            ->whereDoesntHave('parent.payments', fn ($q) => $q->whereNotNull('paid_date'));

        // High-water mark across every known student, so a child going unpaid
        // (a removal) advances the consumer's `since` just like an addition.
        $lastChangedAt = Child::query()->whereNotNull('student_number')->max('updated_at');
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

        $removed = $unpaid()
            ->when($since, fn ($q) => $q->where('updated_at', '>=', $since))
            ->orderBy('student_number')
            ->pluck('student_number')
            ->map(fn ($number) => (string) $number)
            ->values();

        return response()->json([
            'last_changed_at' => $lastChangedAt ? (string) $lastChangedAt : null,
            'count' => $count,
            'students' => $students,
            'removed' => $removed,
        ]);
    }
}
