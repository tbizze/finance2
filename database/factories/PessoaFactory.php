<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pessoa>
 */
class PessoaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $tipo = $this->faker->randomElement(['F','J']);
        if($tipo == 'F'){
            $cpf_cnpj = $this->faker->cpf(false);
            $name = $this->faker->name();
            $codigo = 'F-' . $this->faker->randomElement(['172','125','126','127','128','173','174','175']);
        }else{
            $cpf_cnpj = $this->faker->cnpj(false);
            $name = $this->faker->company();
            $codigo = 'J-' . $this->faker->randomElement(['042','025','026','027','028','073','074','075']);
        }
        return [
            // 'notas','ativo','codigo','cpf_cnpj','rg_inscricao','nome_razao','apelido_fantasia'

            'nome_razao' => $name,
            'apelido_fantasia' => $name,
            'notas' => $this->faker->sentence(2),
            'codigo' => $codigo,
            'cpf_cnpj' => $cpf_cnpj,
            'rg_inscricao' => null,
            'ativo' => true,
        ];
    }
}
