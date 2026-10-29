<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Employee;
use App\Models\School;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Employee>
 */
class EmployeeFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'school_id' => School::factory(),
            'nama' => fake()->name(),
            'jenis' => fake()->randomElement(['guru_mapel', 'guru_kelas', 'tendik']),
            'status_kepegawaian' => fake()->randomElement(['pns', 'pppk', 'honorer', 'kontrak']),
            'status_aktif' => true,
        ];
    }
}
