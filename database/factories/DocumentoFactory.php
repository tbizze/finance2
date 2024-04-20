<?php

namespace Database\Factories;

use App\Models\DocumentoClasse;
use App\Models\DocumentoStatus;
use App\Models\DocumentoTipo;
use App\Models\Pessoa;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Documento>
 */
class DocumentoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $data_venc = $this->randomDate()->format('Y-m-d');
        $now = date('Y-m-d');

        //dd($this->getRandomStatus());

        if ($data_venc > $now) {
            $status = $this->getRandomStatus(DocumentoStatus::class);
        }else{
            $status = 3; // status => VENCIDO
        }
        //dd($data_venc, $now, $status);


        return [
            // 'data_emissao','data_venc','notas','codigo','condicao','parcela','documento_tipo_id','documento_classe_id','pessoa_id','status_id'

            'data_emissao' => $data_venc,
            'data_venc' => $data_venc,
            'notas' => $this->faker->sentence(2),
            'codigo' => $this->faker->randomNumber(4),
            'condicao' => 1,
            'parcela' => 0,
            'documento_tipo_id' => $this->getRandom(DocumentoTipo::class),
            'documento_classe_id' => $this->getRandom(DocumentoClasse::class),
            'documento_status_id' => $status,
            'pessoa_id' => $this->getRandom(Pessoa::class),
        ];
    }

    private function randomDate()
    {
        /* $random = $this->faker->randomElement([
            '-5 days','-15 days','-25 days','-1 month','-2 month','-3 month','-4 month','-5 month',
            '-5 days','-15 days','-25 days','-1 month','-2 month','-3 month','-4 month','-5 month',
        ]); */
        return $this->faker->dateTimeBetween('-5 month', '+2 month');
    }
    private function getRandom($model)
    {
        $random = $model::all()->random(1)->pluck('id'); 
        return $random[0];
    }
    private function getRandomStatus()
    {
        //$random = $model::all()->random(1)->pluck('id'); 
        $random = DocumentoStatus::query()
            ->whereNotIn('id', [3])
            ->get()
            ->random(1)->pluck('id'); 
        return $random[0];
    }
}
