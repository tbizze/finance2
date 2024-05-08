<?php

namespace App\Http\Controllers;

use App\Models\Jobcard;
use Illuminate\Http\Request;

class JobcardController extends Controller
{
    public function index()
    {
        $cards = Jobcard::query()
            ->select([
                'job_comp_code',
                \DB::raw("DATE_FORMAT(job_received_date, '%Y-%m') AS month"),
                \DB::raw("COUNT(id) AS invoices"),
                \DB::raw("SUM(job_invoice_amount) AS amount"),
            ])
            ->groupBy('job_comp_code')
            ->groupBy('month')
            ->orderBy('month')
            //->dd();
            ->get();
        dump($cards->toArray());

        $report = [];
        $cards->each(function ($item) use (&$report) {
            $report[$item->month][$item->job_comp_code] = [
                'count' => $item->invoices,
                'amount' => $item->amount,
            ];
        });
        dump($report);

        $job_comp_codes = $cards->pluck('job_comp_code')
            ->sortBy('job_comp_code')
            ->unique();
        dump($job_comp_codes);

        return view('test-jobcard', compact('report', 'job_comp_codes'));
    }
}
