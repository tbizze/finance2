<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('jobcards', function (Blueprint $table) {
            $table->id();
            $table->string('job_comp_code')->nullable();
            $table->integer('job_enq_no')->nullable();
            $table->date('job_received_date')->nullable();
            $table->date('job_invoice_date')->nullable();
            $table->integer('job_invoice_amount')->nullable();
            $table->string('job_status')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jobcards');
    }
};
