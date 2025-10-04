<?php


namespace App\Http\Controllers;

use App\Services\TranslationService;
use Illuminate\Http\Request;

class TranslationController extends Controller
{
    public function index(Request $request, string $locale)
    {
        if (!in_array($locale, ['fr', 'en', 'nl'])) {
            abort(400, 'Langue non supportée');
        }

        return response()->json([
            'locale' => $locale,
            'translations' => TranslationService::getTranslations($locale),
        ]);
    }
}
