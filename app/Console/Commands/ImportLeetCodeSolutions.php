<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\LeetCodeSolutionService;

class ImportLeetCodeSolutions extends Command
{
    protected $signature = 'leetcode:import-solutions';
    protected $description = 'Import solutions for LeetCode questions';

    public function handle()
    {
        $service = new LeetCodeSolutionService($this);
        $service->importSolutions();
    }
} 