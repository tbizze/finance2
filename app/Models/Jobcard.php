<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jobcard extends Model
{
    use HasFactory;

    /**
     * Lista de campos em que é permitido a persistência no BD.
     */
    protected $fillable = [
        'job_comp_code', 'job_enq_no', 'job_received_date', 'job_invoice_date', 'job_invoice_amount', 'job_status'
    ];
}
