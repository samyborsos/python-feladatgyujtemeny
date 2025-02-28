<?php

namespace App\Services;

use Stichoza\GoogleTranslate\GoogleTranslate;
use Illuminate\Support\Facades\Log;

class AITranslationService
{
    public function translateDescription(string $description): string
    {
        try {
            dump('Starting translation process...');
            dump('Original text:', $description);

            $tr = new GoogleTranslate();
            $tr->setSource('en');
            $tr->setTarget('hu');

            $translatedText = $tr->translate($description);

            dump('Translated text:', $translatedText);

            return $translatedText;
        } catch (\Exception $e) {
            dump('Translation failed!', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            Log::error('Translation failed', ['error' => $e->getMessage()]);
            return $description;
        }
    }
}
