<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\School;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<School>
 */
class SchoolFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $nama = 'SMP Negeri '.fake()->unique()->numberBetween(1, 9999).' '.fake()->city();

        return [
            'nama' => $nama,
            'slug' => Str::slug($nama).'-'.Str::lower(Str::random(6)),
            'jenjang' => 'SMP',
            'status' => 'aktif',
            'status_langganan' => 'active',
        ];
    }
}
