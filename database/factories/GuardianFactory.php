<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Guardian;
use App\Models\School;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Guardian>
 */
class GuardianFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'school_id' => School::factory(),
            'nama' => fake()->name(),
            'hubungan' => fake()->randomElement(['ayah', 'ibu', 'wali']),
            'no_wa' => '628'.fake()->unique()->numerify('#########'),
        ];
    }
}
