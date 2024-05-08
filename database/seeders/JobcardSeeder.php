<?php

namespace Database\Seeders;

use App\Models\Jobcard;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class JobcardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // '', '', '', '', '', ''
        //
        $items = [
            // --- RECEITAS
            [
                'job_comp_code' => 'A',
                'job_enq_no' => '101',
                'job_received_date' => '2020-06-15',
                'job_invoice_date' => '2020-06-15',
                'job_invoice_amount' => '50',
                'job_status' => 'Invoiced',
            ],
            [
                'job_comp_code' => 'A',
                'job_enq_no' => '102',
                'job_received_date' => '2020-06-16',
                'job_invoice_date' => '2020-06-16',
                'job_invoice_amount' => '60',
                'job_status' => 'Invoiced',
            ],
            [
                'job_comp_code' => 'A',
                'job_enq_no' => '103',
                'job_received_date' => '2020-07-20',
                'job_invoice_date' => '2020-07-20',
                'job_invoice_amount' => '45',
                'job_status' => 'Invoiced',
            ],
            [
                'job_comp_code' => 'A',
                'job_enq_no' => '104',
                'job_received_date' => '2020-08-25',
                'job_invoice_date' => '2020-08-25',
                'job_invoice_amount' => '45',
                'job_status' => 'Invoiced',
            ],
            [
                'job_comp_code' => 'A',
                'job_enq_no' => '105',
                'job_received_date' => '2020-08-17',
                'job_invoice_date' => '2020-08-17',
                'job_invoice_amount' => '55',
                'job_status' => 'Invoiced',
            ],
            [
                'job_comp_code' => 'A',
                'job_enq_no' => '106',
                'job_received_date' => '2020-08-17',
                'job_invoice_date' => null,
                'job_invoice_amount' => null,
                'job_status' => 'Received',
            ],
            [
                'job_comp_code' => 'B',
                'job_enq_no' => '201',
                'job_received_date' => '2020-07-15',
                'job_invoice_date' => '2020-07-15',
                'job_invoice_amount' => '45',
                'job_status' => 'Invoiced',
            ],
            [
                'job_comp_code' => 'B',
                'job_enq_no' => '202',
                'job_received_date' => '2020-07-16',
                'job_invoice_date' => '2020-07-16',
                'job_invoice_amount' => '35',
                'job_status' => 'Invoiced',
            ],
            [
                'job_comp_code' => 'B',
                'job_enq_no' => '203',
                'job_received_date' => '2020-08-17',
                'job_invoice_date' => '2020-08-17',
                'job_invoice_amount' => '25',
                'job_status' => 'Invoiced',
            ],
            [
                'job_comp_code' => 'B',
                'job_enq_no' => '204',
                'job_received_date' => '2020-08-17',
                'job_invoice_date' => null,
                'job_invoice_amount' => null,
                'job_status' => 'Received',
            ],

        ];

        foreach ($items as $item) {
            Jobcard::create($item);
        }
    }
}
