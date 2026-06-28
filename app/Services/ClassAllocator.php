<?php

namespace App\Services;

/**
 * Maps a child's day-school year to the school class they're allocated to,
 * using the configurable rule in config/integration.php. Both subjects get the
 * same class at payment; an admin can diverge them afterwards.
 */
class ClassAllocator
{
    /**
     * The allocated class name (e.g. "Class C") for a day-school year, or null
     * if the year isn't in the rule (left for an admin to set manually).
     */
    public function classForGrade(?string $daySchoolYear): ?string
    {
        if ($daySchoolYear === null) {
            return null;
        }

        $map = config('integration.allocation', []);

        return $map[$daySchoolYear] ?? null;
    }

    /**
     * The distinct class names the rule can produce, e.g. ['Class A', …, 'Class E'].
     * Used to populate the admin allocation dropdowns.
     *
     * @return array<int, string>
     */
    public function availableClasses(): array
    {
        return array_values(array_unique(array_values(config('integration.allocation', []))));
    }
}
