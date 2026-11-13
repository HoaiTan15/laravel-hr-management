<?php

namespace Database\Factories;

use App\Models\PenaltyRule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PenaltyRule>
 */
class PenaltyRuleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array{name: string, type: string, amount: int, scope: string, description: null, is_active: bool}
     */
    public function definition(): array
    {
        return [
            'name' => 'Quy định '.fake()->unique()->words(3, true),
            'type' => 'late',
            'amount' => fake()->numberBetween(1, 10) * 10000,
            'scope' => 'company',
            'description' => null,
            'is_active' => true,
        ];
    }
}
