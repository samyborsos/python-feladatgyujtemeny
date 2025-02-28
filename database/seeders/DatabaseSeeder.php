<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Question;
use App\Services\CodeWarsService;
use App\Services\LeetCodeService;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info("\n🚀 Starting database seeding process...\n");

        // Initialize service
        $leetcode = new LeetCodeService($this->command);

        // Test API connection first
        if (!$leetcode->testApiConnection()) {
            $this->command->error('❌ Could not connect to local API. Make sure it\'s running on http://127.0.0.1:3000');
            return;
        }

        $totalStats = [
            'leetcode' => 0,
            'codewars' => 0,
            'errors' => 0
        ];

        // Process each difficulty level
        foreach ([1] as $difficulty) {
            $this->command->info("\n📚 Processing difficulty {$difficulty} questions...");

            // Fetch LeetCode questions - this now handles its own database operations
            $success = $leetcode->fetchQuestions($difficulty);

            if (!$success) {
                $this->command->warn('Failed to fetch questions for difficulty ' . $difficulty);
                $totalStats['errors']++;
                continue;
            }

            // Update stats based on questions in database
            $totalStats['leetcode'] = Question::where('source', 'LeetCode API')->count();
        }

        // Display statistics
        $this->displayStats($totalStats);
    }

    private function displayStats($stats)
    {
        $this->command->info("\n\n📊 Seeding Statistics:");
        $this->command->table(
            ['Metric', 'Count'],
            [
                ['LeetCode Questions', $stats['leetcode']],
                ['CodeWars Questions', $stats['codewars']],
                ['Total Questions', $stats['leetcode'] + $stats['codewars']],
                ['Errors Encountered', $stats['errors']]
            ]
        );
        $this->command->info("\n✨ Database seeding completed!");
    }
}
