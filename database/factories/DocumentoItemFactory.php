<?php

namespace Database\Factories;

use App\Models\Documento;
use App\Models\PlanoCtaItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DocumentoItem>
 */
class DocumentoItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $doc = $this->getDocumento();
        $documento_id = $doc->id;        

        switch ($doc->documento_tipo_id) {
            case 1:
            case 3:
            case 5:
                $plano_cta = $this->getPlanoCtaItem('D');
                break;
            case 2:
            case 4:
                $plano_cta = $this->getPlanoCtaItem('C');
                break;
            case 6:
                $plano_cta = 1; // Mov. mesmo titular.
                break;
        }
        //dd($documento_id, $plano_cta);

        return [
            // 'descricao','notas','valor','documento_id','plano_cta_item_id'

            'descricao' => $this->randomProdutos(),
            'notas' => $this->faker->sentence(3),
            'valor' => $this->faker->randomFloat(2,10,500),
            'documento_id' => $documento_id,
            'plano_cta_item_id' => $plano_cta,
        ];
    }

    private function getDocumento()
    {
        $random = Documento::all()->random(1);
        return $random[0];
    }
    private function getPlanoCtaItem($natureza)
    {
        $random = PlanoCtaItem::query()
            ->where('natureza', $natureza)
            ->where('lcto', true)
            ->get()->random(1)->pluck('id');
        return $random[0];
    }
    private function randomProdutos()
    {
        return $this->faker->randomElement([
            'Roupas',
            'Calçados',
            'Eletrônicos',
            'Artigos de Higiene',
            'Artigos de Limpeza',
            'Eletrodomésticos',
            'Alimentos',
            'Medicamentos',
            'Cursos e Capacitações',
            'Consultorias',
            'Livros',
            'Artigos esportivos',
            'Bebidas',
            'Móveis',
            'Artigos para pets',
            'Itens de decoração',
            'Artigos de decoração',
            'Tapetes',
            'Quadros',
            'Aromatizador de ambientes',
            'Mantas para sofá',
            'Capas e almofadas',
            'Cortinas',
            'Vasos',
            'Objetos de decoração',
            'Espelhos',
            'Luminárias',
            'Porta-retratos',
        ]);
    }
}
