<?php

namespace Database\Factories;

use App\Models\Child;
use App\Models\ParentModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * Class ChildFactory
 *
 * Generates fake child data for testing.
 */
class ChildFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Child::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'parent_id' => ParentModel::factory(), // By default, create a parent if not specified
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'gender' => $this->faker->randomElement(['Male', 'Female']),
            'date_of_birth' => $this->faker->dateTimeBetween('-18 years', '-2 years')->format('Y-m-d'),
            'residency_status' => $this->faker->randomElement(['Temporary Resident', 'Permanent Resident', 'Citizen']),
            'day_school_name' => $this->faker->company,
            'day_school_year' => $this->faker->numberBetween(1, 12),
            'allergies' => $this->faker->boolean ? $this->faker->word : null,
            'special_needs' => $this->faker->boolean ? $this->faker->sentence : null,
            'dhamma_class' => $this->faker->randomElement(['Grade 1', 'Grade 2', 'Grade 3']),
            'sinhala_class' => $this->faker->randomElement(['Level A', 'Level B', 'Level C']),
            'student_number' => $this->faker->unique()->numberBetween(1000, 9999),
            'year_of_first_registration' => Carbon::now()->year,
        ];
    }
}
