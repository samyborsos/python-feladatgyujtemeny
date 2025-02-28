<?php

namespace Database\Seeders;

use App\Models\Question;
use Illuminate\Database\Seeder;

class QuestionSeeder extends Seeder
{
    public function run(): void
    {
        Question::create([
            'title_hu' => 'Számok összege',
            'title_en' => 'Sum of Numbers',
            'description_hu' => 'Valósítson meg egy def sum_numbers(numbers) függvényt, amely egy számokat tartalmazó listát kap paraméterként.

A függvény térjen vissza az összes szám összegével.

A numbers paraméter egy egész számokat tartalmazó lista lesz.
A függvény ne módosítsa a bemeneti listát.',
            'description_en' => 'Implement a function called sum_numbers that takes a list of numbers as a parameter.',
            'initial_code' => "def sum_numbers(numbers):\n    pass",
            'solution' => "def sum_numbers(numbers):\n    return sum(numbers)",
            'difficulty' => '1',
            'test_cases' => [
                [
                    'input' => "print(sum_numbers([1, 2, 3]))",
                    'expected' => "6"
                ],
                [
                    'input' => "print(sum_numbers([-1, 0, 1]))",
                    'expected' => "0"
                ],
                [
                    'input' => "print(sum_numbers([10, -5, 3, 2]))",
                    'expected' => "10"
                ]
            ],
            'source' => 'Custom Question'
        ]);
    }
}
