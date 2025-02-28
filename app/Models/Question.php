<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\MarkdownConverter;
use App\Services\AITranslationService;
use Stichoza\GoogleTranslate\GoogleTranslate;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'title_en',
        'title_hu',
        'description_en',
        'description_hu',
        'difficulty',
        'source',
        'initial_code',
        'test_cases'
    ];

    protected $casts = [
        'test_cases' => 'array'
    ];

    public function getDescriptionHtmlAttribute()
    {
        if (is_null($this->description_en)) {
            return '';
        }

        $environment = new Environment([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
            'max_nesting_level' => 100,
        ]);

        $environment->addExtension(new CommonMarkCoreExtension());
        $environment->addExtension(new GithubFlavoredMarkdownExtension());

        $converter = new MarkdownConverter($environment);
        return $converter->convert($this->description_en);
    }

    public function getDescriptionHuAttribute($value)
    {
        // If we already have a Hungarian translation, return it
        if (!empty($value)) {
            return $value;
        }

        // If no Hungarian translation exists but we have English description
        if (!empty($this->description_en)) {
            // Translate English to Hungarian
            $translator = new GoogleTranslate();
            $translator->setSource('en');
            $translator->setTarget('hu');

            $translated = $translator->translate($this->description_en);

            // Store the Hungarian translation
            $this->update(['description_hu' => $translated]);

            return $translated;
        }

        // If we somehow have neither, return empty string
        return '';
    }

    // When setting description_hu, move it to description if description is empty
    public function setDescriptionHuAttribute($value)
    {
        $this->attributes['description_hu'] = $value;
    }
}
