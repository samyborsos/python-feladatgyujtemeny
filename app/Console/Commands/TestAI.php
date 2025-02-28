<?php

namespace App\Console\Commands;

use App\Services\AIService;
use Illuminate\Console\Command;

class TestAI extends Command
{
    protected $signature = 'ai:test';
    protected $description = 'Test AI Service with LeetCode format';

    public function handle()
    {
        $this->info("🤖 Testing AI Service...");

        $aiService = new AIService($this);

        // Test Solution Generation
        $this->info("\n📝 Testing Solution Generation:");
        $title = "Two Sum";
        $description = "Given an array of integers nums and an integer target, return indices of the two numbers such that they add up to target.";

        $this->line("\nTitle: " . $title);
        $this->line("Description: " . $description);

        $solution = $aiService->generateSolution($title, $description);

        if ($solution) {
            $this->info("\n✅ Solution Generated Successfully:");
            $this->line($solution);
        }

        // Test Description Generation
        $this->info("\n📝 Testing Description Generation:");
        $description = $aiService->generateDescription($title, $description);

        if ($description) {
            $this->info("\n✅ Description Generated Successfully:");
            $this->line($description);
        }

        // Test Error Handling
        $this->info("\n🧪 Testing Error Handling:");
        $badSolution = $aiService->generateSolution("", "");

        if (!$badSolution) {
            $this->info("✅ Error handling working correctly");
        }

        $this->info("\n🎉 Test Complete!");
    }
}
