<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Holiday>
 */
class HolidayFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'holiday_date' => $this->faker->date(),
            'name' => $this->faker->words(2, true),
            'type' => $this->faker->randomElement(['national', 'local', 'observance']),
            'is_recurring' => $this->faker->boolean(20),
        ];
    }
}
