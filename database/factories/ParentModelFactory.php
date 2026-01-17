<?php

namespace Database\Factories;

use App\Models\ParentModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * Class ParentModelFactory
 *
 * Generates fake parent/guardian data for testing.
 */
class ParentModelFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ParentModel::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'parent1_first_name' => $this->faker->firstName,
            'parent1_last_name' => $this->faker->lastName,
            'parent1_email' => $this->faker->unique()->safeEmail,
            'parent1_phone' => $this->faker->phoneNumber,
            'parent2_first_name' => $this->faker->optional()->firstName,
            'parent2_last_name' => $this->faker->optional()->lastName,
            'parent2_email' => $this->faker->optional()->safeEmail,
            'parent2_phone' => $this->faker->optional()->phoneNumber,
            'emergency_contact_name' => $this->faker->name,
            'emergency_contact_phone' => $this->faker->phoneNumber,
            'relationship_to_family' => $this->faker->randomElement(['Aunt', 'Uncle', 'Grandparent', 'Family Friend']),
            'update_token' => null,
            'token_expires_at' => null,
        ];
    }
}
