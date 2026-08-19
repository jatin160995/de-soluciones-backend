<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Console\Commands\GenerateAgentCommissionStatements;
use Illuminate\Support\Facades\Schedule;


Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command(GenerateAgentCommissionStatements::class)
    ->monthlyOn(1, '02:00')
    ->timezone('America/Tegucigalpa');
