<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class QuestionFactory extends Factory
{
    private function generatePythonQuestion(): array
    {
        $questionTypes = [
            [
                'title' => 'Lista elemek szűrése',
                'function_name' => 'filter_numbers',
                'params' => ['numbers'],
                'description' => 'Szűrje ki a listából a pozitív számokat.',
                'test_cases' => [
                    ['input' => '[-1, 2, -3, 4, -5]', 'output' => '[2, 4]'],
                    ['input' => '[0, -1, -2, -3]', 'output' => '[]'],
                    ['input' => '[1, 2, 3, 4, 5]', 'output' => '[1, 2, 3, 4, 5]']
                ]
            ],
            [
                'title' => 'Szöveg feldolgozás',
                'function_name' => 'count_vowels',
                'params' => ['text'],
                'description' => 'Számolja meg a magánhangzókat a szövegben.',
                'test_cases' => [
                    ['input' => '"hello world"', 'output' => '3'],
                    ['input' => '"python"', 'output' => '1'],
                    ['input' => '"aeiou"', 'output' => '5']
                ]
            ],
            [
                'title' => 'Számsorozat',
                'function_name' => 'fibonacci',
                'params' => ['n'],
                'description' => 'Generálja le a Fibonacci sorozat első n elemét.',
                'test_cases' => [
                    ['input' => '5', 'output' => '[0, 1, 1, 2, 3]'],
                    ['input' => '3', 'output' => '[0, 1, 1]'],
                    ['input' => '7', 'output' => '[0, 1, 1, 2, 3, 5, 8]']
                ]
            ]
        ];

        $question = $this->faker->randomElement($questionTypes);
        $difficulty = $this->faker->numberBetween(1, 5);

        $description = sprintf(
            'Valósítson meg egy def %s(%s) függvényt, amely %s',
            $question['function_name'],
            implode(', ', $question['params']),
            $question['description']
        );

        $initial_code = sprintf(
            "def %s(%s):\n    pass",
            $question['function_name'],
            implode(', ', $question['params'])
        );

        $test_cases = array_map(function ($test) use ($question) {
            return [
                'input' => sprintf("print(%s(%s))", $question['function_name'], $test['input']),
                'expected' => $test['output']
            ];
        }, $question['test_cases']);

        return [
            'title_hu' => $question['title'],
            'description_hu' => $description,
            'initial_code' => $initial_code,
            'difficulty' => (string)$difficulty,
            'test_cases' => $test_cases
        ];
    }

    public function definition(): array
    {
        return $this->generatePythonQuestion();
    }
}
