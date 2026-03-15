<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Faculty>
 */
class FacultyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'department_id' => Department::factory(),
            'faculty_code' => strtoupper($this->faker->unique()->bothify('FC###')),
            'biometric_id' => strtoupper($this->faker->unique()->bothify('BIO####')),
            'first_name' => $this->faker->firstName(),
            'middle_name' => $this->faker->optional()->firstName(),
            'last_name' => $this->faker->lastName(),
            'phone' => $this->faker->optional()->phoneNumber(),
            'employment_type' => $this->faker->optional()->randomElement(['full-time', 'part-time']),
            'date_hired' => $this->faker->optional()->date(),
            'is_active' => true,
        ];
    }
}
