<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\School;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Student>
 */
class StudentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'school_id' => School::factory(),
            'nis' => (string) fake()->unique()->numberBetween(1000, 999999),
            'nisn' => (string) fake()->unique()->numerify('##########'),
            'nama' => fake()->name(),
            'jk' => fake()->randomElement(['L', 'P']),
            'status' => 'aktif',
        ];
    }
}
