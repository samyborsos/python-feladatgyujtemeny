<?php

return [
    'model' => env('AI_MODEL', 'phi'),
    'url' => env('AI_URL', 'http://localhost:11434'),
    'timeout' => env('AI_TIMEOUT', 30),
    'temperature' => env('AI_TEMPERATURE', 0.7),
];