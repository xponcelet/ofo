<?php

namespace App\Services;

use Illuminate\Support\Facades\File;

class TranslationService
{
    public static function getVueTranslations(string $locale): array
    {
        $base = base_path("lang/{$locale}/vue");
        $out = [];

        if (File::isDirectory($base)) {
            foreach (File::allFiles($base) as $file) {
                $name = pathinfo($file->getFilename(), PATHINFO_FILENAME); // ex: "dashboard"
                /** @var array $arr */
                $arr = require $file->getRealPath();
                $out[$name] = is_array($arr) ? $arr : [];
            }
        }

        return $out; // ex: ['dashboard' => [...], 'common' => [...]]
    }
}
