<?php

namespace App\Console\Commands;

use App\Models\Child;
use App\Services\ClassAllocator;
use Illuminate\Console\Command;

/**
 * Allocate a class to paid children that don't have one yet, using the rule in
 * config/integration.php (their day-school year -> class). Children are
 * allocated automatically at payment, so this is for back-filling already-paid
 * children that predate the integration (e.g. at one-time rollout), and as an
 * ongoing safety net. Idempotent: it only touches children whose allocation is
 * currently null, and never overrides an admin's manual allocation.
 */
class AllocateMissing extends Command
{
    protected $signature = 'integration:allocate-missing {--dry-run : List what would change without writing anything}';

    protected $description = 'Allocate a class to paid children that have none yet, from the day-school-year rule.';

    public function handle(ClassAllocator $allocator): int
    {
        $dryRun = (bool) $this->option('dry-run');

        // Same "paid" definition as the integration API: a student_number set
        // and a parent payment with a paid_date. Only those missing a Buddhism
        // allocation are candidates.
        $candidates = Child::query()
            ->whereNotNull('student_number')
            ->whereNull('allocated_dhamma_class')
            ->whereHas('parent.payments', fn ($q) => $q->whereNotNull('paid_date'))
            ->orderBy('student_number')
            ->get();

        $allocated = 0;
        $skipped = [];

        foreach ($candidates as $child) {
            $class = $allocator->classForGrade($child->day_school_year);

            if ($class === null) {
                $skipped[] = "#{$child->student_number} {$child->first_name} {$child->last_name} (year: ".($child->day_school_year ?? 'none').')';

                continue;
            }

            if ($dryRun) {
                $this->line("would allocate #{$child->student_number} {$child->first_name} {$child->last_name} -> {$class}");
            } else {
                $child->update([
                    'allocated_dhamma_class' => $class,
                    'allocated_sinhala_class' => $class,
                ]);
            }

            $allocated++;
        }

        $verb = $dryRun ? 'Would allocate' : 'Allocated';
        $this->info("{$verb} {$allocated} of {$candidates->count()} unallocated paid children.");

        foreach ($skipped as $line) {
            $this->warn("No rule for {$line} — left for an admin to set.");
        }

        return self::SUCCESS;
    }
}
