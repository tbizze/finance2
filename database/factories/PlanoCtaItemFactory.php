<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PlanoCtaItem>
 */
class PlanoCtaItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // 'nome','notas','ativo','parent','codigo','cod_externo','natureza','lcto','exibir_dre','plano_cta_id'
            'nome' => $this->faker->sentence(2),
            'notas' => $this->faker->sentence(3),
            'ativo' => true,
            /* 'parent' => ,
            'codigo' => ,
            'cod_externo' => ,
            'natureza' => ,
            'lcto' => $lcto,
            'exibir_dre' => true,
            'plano_cta_id' => 1, */
        ];
    }
}
