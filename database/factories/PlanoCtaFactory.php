<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PlanoCta>
 */
class PlanoCtaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // 'codigo','nome','notas','ativo','mascara','niveis'
            'codigo' => '4E001',
            'nome' => $this->faker->sentence(2),
            'notas' => $this->faker->sentence(3),
            'ativo' => true,
            'mascara' => '0.0.00.000',
            'niveis' => 4,
        ];
    }
}
