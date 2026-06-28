<?php

namespace Tests\Unit;

use App\Services\ClassAllocator;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ClassAllocatorTest extends TestCase
{
    public static function gradeProvider(): array
    {
        return [
            ['Pre School', 'Class A'],
            ['Kindergarten', 'Class A'],
            ['Grade 1', 'Class A'],
            ['Grade 2', 'Class B'],
            ['Grade 3', 'Class C'],
            ['Grade 4', 'Class C'],
            ['Grade 5', 'Class D'],
            ['Grade 6', 'Class D'],
            ['Grade 7', 'Class E'],
            ['Grade 8', 'Class E'],
            ['Grade 12', 'Class E'],
        ];
    }

    #[DataProvider('gradeProvider')]
    public function test_it_maps_each_grade_to_a_class(string $grade, string $expected): void
    {
        $this->assertSame($expected, (new ClassAllocator)->classForGrade($grade));
    }

    public function test_unknown_or_null_grade_yields_no_allocation(): void
    {
        $allocator = new ClassAllocator;
        $this->assertNull($allocator->classForGrade('Grade 99'));
        $this->assertNull($allocator->classForGrade(null));
    }

    public function test_available_classes_are_the_distinct_targets(): void
    {
        $this->assertSame(
            ['Class A', 'Class B', 'Class C', 'Class D', 'Class E'],
            (new ClassAllocator)->availableClasses(),
        );
    }
}
