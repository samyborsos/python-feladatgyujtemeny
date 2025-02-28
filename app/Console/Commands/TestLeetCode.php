<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\LeetCodeService;

class TestLeetCode extends Command
{
    protected $signature = 'leetcode:test';
    protected $description = 'Test LeetCode playground extraction';

    public function handle()
    {
        $leetcode = new LeetCodeService($this);
        $leetcode->testPlaygroundExtraction();
    }
} 