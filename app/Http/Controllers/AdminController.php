<?php

namespace App\Http\Controllers;

use App\Mail\ClassAllocationChanged;
use App\Models\Child;
use App\Models\ParentModel;
use App\Services\ClassAllocator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Class AdminController
 *
 * Manages admin-specific features like listing parent/child data.
 */
class AdminController extends Controller
{
    /**
     * Show all parents & children in a list (DataTables or plain HTML).
     *
     * @return View
     */
    public function showParentStudentList()
    {
        $parents = ParentModel::with('children')->get();
        Log::info('Admin viewing parent & child list');

        return view('admin.parent_child_list', compact('parents'));
    }

    public function showOrientationList()
    {
        $parents = ParentModel::with('children')->get();
        Log::info('Admin viewing orientation list');

        return view('admin.orientation_list', compact('parents'));
    }

    /**
     * Medical reference: every enrolled student who has a real allergy OR a
     * real special need recorded (anything other than null, blank, or "None").
     * Includes the child's class and who to contact, so staff can act quickly.
     */
    public function showAllergies()
    {
        $children = Child::query()
            ->whereNotNull('student_number')
            ->where(function ($q) {
                $q->where(fn ($a) => $this->scopeHasRealValue($a, 'allergies'))
                    ->orWhere(fn ($s) => $this->scopeHasRealValue($s, 'special_needs'));
            })
            ->with('parent:id,parent1_first_name,parent1_last_name,parent1_phone,parent1_email,emergency_contact_name,emergency_contact_phone')
            ->orderBy('first_name')
            ->get();

        Log::info('Admin viewing allergies/medical list', ['count' => $children->count()]);

        return view('admin.allergies', compact('children'));
    }

    /**
     * Constrain a query so the given column holds a meaningful value — i.e. not
     * null, not blank, and not "None" (any case). Used for the allergies and
     * special-needs medical columns, which both use "None" as the empty marker.
     */
    private function scopeHasRealValue($query, string $column)
    {
        return $query->whereNotNull($column)
            ->where($column, '!=', '')
            ->whereRaw("LOWER(TRIM({$column})) != ?", ['none']);
    }

    /**
     * Worklist of *paid* students who still need a class for at least one
     * subject — e.g. someone whose day-school year wasn't in the auto-allocation
     * rule. Lets an admin fill the gaps. To move an already-allocated student
     * (incl. imported ones), use the Class Relocation search instead. Saving
     * bumps children.updated_at, which the attendance app syncs on.
     */
    public function showUnallocated(ClassAllocator $allocator)
    {
        $children = Child::query()
            ->whereNotNull('student_number')
            ->whereHas('parent.payments', fn ($q) => $q->whereNotNull('paid_date'))
            ->where(function ($q) {
                $q->whereNull('allocated_dhamma_class')
                    ->orWhereNull('allocated_sinhala_class');
            })
            ->with('parent:id,parent1_first_name,parent1_last_name')
            ->orderBy('student_number')
            ->get();

        $classes = $allocator->availableClasses();
        Log::info('Admin viewing unallocated students');

        return view('admin.unallocated', compact('children', 'classes'));
    }

    /**
     * Search any enrolled student by name or student number and relocate them
     * to a different class. Loads no rows until a search is run, so the page
     * opens instantly regardless of how many students exist. Unlike the
     * unallocated worklist, this finds already-allocated students too (incl.
     * imported ones).
     */
    public function searchRelocation(Request $request, ClassAllocator $allocator)
    {
        $q = trim((string) $request->query('q', ''));

        $children = collect();
        if ($q !== '') {
            $children = Child::query()
                ->whereNotNull('student_number')
                ->where(function ($builder) use ($q) {
                    $builder->where('first_name', 'like', "%{$q}%")
                        ->orWhere('last_name', 'like', "%{$q}%")
                        ->orWhere('student_number', 'like', "%{$q}%");
                })
                ->with('parent:id,parent1_first_name,parent1_last_name')
                ->orderBy('student_number')
                ->limit(100)
                ->get();
        }

        $classes = $allocator->availableClasses();
        Log::info('Admin searching class relocation', ['has_query' => $q !== '']);

        return view('admin.class_relocation', compact('children', 'classes', 'q'));
    }

    /**
     * Persist edited allocations. Each value must be one of the rule's classes
     * or blank (clear it).
     */
    public function updateAllocations(Request $request, ClassAllocator $allocator)
    {
        $allowed = $allocator->availableClasses();

        $validated = $request->validate([
            'allocations' => ['array'],
            'allocations.*.dhamma' => ['nullable', 'string', Rule::in($allowed)],
            'allocations.*.sinhala' => ['nullable', 'string', Rule::in($allowed)],
        ]);

        $notified = 0;

        foreach (($validated['allocations'] ?? []) as $studentNumber => $values) {
            $child = Child::with('parent')->where('student_number', $studentNumber)->first();
            if (! $child) {
                continue;
            }

            $newDhamma = $values['dhamma'] ?? null;
            $newSinhala = $values['sinhala'] ?? null;

            // Only the subjects that actually changed value are worth saving or
            // notifying about — re-saving the page with no edits is a no-op.
            $changes = [];
            if ($child->allocated_dhamma_class !== $newDhamma) {
                $changes[] = ['subject' => 'Buddhism', 'from' => $child->allocated_dhamma_class, 'to' => $newDhamma];
            }
            if ($child->allocated_sinhala_class !== $newSinhala) {
                $changes[] = ['subject' => 'Sinhala', 'from' => $child->allocated_sinhala_class, 'to' => $newSinhala];
            }

            if ($changes === []) {
                continue;
            }

            $child->update([
                'allocated_dhamma_class' => $newDhamma,
                'allocated_sinhala_class' => $newSinhala,
            ]);

            if ($this->notifyAllocationChange($child, $changes)) {
                $notified++;
            }
        }

        $status = $notified > 0
            ? "Allocations updated. {$notified} ".($notified === 1 ? 'family was' : 'families were').' notified by email.'
            : 'Allocations updated.';

        // Return to whichever page submitted the form (the search page keeps
        // the admin on their results); fall back to the unallocated worklist.
        // Only same-site /admin/ paths are honoured, to avoid an open redirect.
        $redirectTo = (string) $request->input('redirect_to', '');
        $target = str_starts_with($redirectTo, '/admin/')
            ? redirect($redirectTo)
            : redirect()->route('admin.unallocated');

        return $target->with('status', $status);
    }

    /**
     * Email a child's parent(s) that their allocated class changed. Returns
     * true if at least one recipient was emailed.
     *
     * @param  array<int, array{subject: string, from: ?string, to: ?string}>  $changes
     */
    private function notifyAllocationChange(Child $child, array $changes): bool
    {
        $parent = $child->parent;
        if (! $parent) {
            return false;
        }

        $recipients = array_filter([$parent->parent1_email, $parent->parent2_email]);
        foreach ($recipients as $email) {
            Mail::to($email)->send(new ClassAllocationChanged($child, $changes));
        }

        return $recipients !== [];
    }

    public function exportCsv()
    {
        $parents = ParentModel::with('children')->get();

        $columns = [
            'Parent1FirstName', 'Parent1LastName', 'Parent1Email', 'Parent1Phone',
            'Parent2FirstName', 'Parent2LastName', 'Parent2Email', 'Parent2Phone',
            'EmergencyContact', 'EmergencyPhone', 'Relationship', 'ChildFirstName',
            'ChildLastName', 'DOB', 'Residency', 'SchoolName', 'SchoolYear', 'Allergies',
            'SpecialNeeds', 'AllocatedDhammaClass', 'AllocatedSinhalaClass',
        ];

        $callback = function () use ($parents, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($parents as $parent) {
                // For each child in children
                foreach ($parent->children as $child) {
                    $row = [
                        $parent->parent1_first_name,
                        $parent->parent1_last_name,
                        $parent->parent1_email,
                        $parent->parent1_phone,
                        $parent->parent2_first_name,
                        $parent->parent2_last_name,
                        $parent->parent2_email,
                        $parent->parent2_phone,
                        $parent->emergency_contact_name,
                        $parent->emergency_contact_phone,
                        $parent->relationship_to_family,
                        $child->first_name,
                        $child->last_name,
                        $child->date_of_birth,
                        $child->residency_status,
                        $child->day_school_name,
                        $child->day_school_year,
                        $child->allergies,
                        $child->special_needs,
                        $child->allocated_dhamma_class,
                        $child->allocated_sinhala_class,
                    ];
                    fputcsv($file, $row);
                }
            }

            fclose($file);
        };

        return response()->streamDownload($callback, 'parents_children.csv');
    }
}
