<?php

namespace Database\Factories;

use App\Models\AllowanceRule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AllowanceRule>
 */
class AllowanceRuleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array{name: string, type: string, amount: int, calculation_basis: string, scope: string, description: null, is_active: bool}
     */
    public function definition(): array
    {
        return [
            'name' => 'Quy định '.fake()->unique()->words(3, true),
            'type' => 'meal',
            'calculation_basis' => 'working_day',
            'amount' => fake()->numberBetween(1, 10) * 10000,
            'scope' => 'company',
            'description' => null,
            'is_active' => true,
        ];
    }
}
