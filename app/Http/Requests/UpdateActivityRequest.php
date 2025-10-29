<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateActivityRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            //  on n'autorise pas de changer d'étape via update
            'step_id'       => ['prohibited'],

            //  only-if-present
            'title'         => ['sometimes','required','string','max:120'],
            'description'   => ['sometimes','nullable','string','max:5000'],
            'location'      => ['sometimes','nullable','string','max:255'],

            // on accepte date + time pour update aussi
            'date'          => ['sometimes','nullable','date'],
            'time'          => ['sometimes','nullable','date_format:H:i'],

            // (on ignore un éventuel start_at envoyé)
            'start_at'      => ['prohibited'],
            'end_at'        => ['sometimes','nullable','date'],

            //  string; on normalise côté serveur
            'external_link' => ['sometimes','nullable','string','max:255'],

            'cost'          => ['sometimes','nullable','numeric','min:0'],
            'currency'      => ['sometimes','nullable','string','size:3'],
            'category'      => ['sometimes','nullable','string','max:100'],

            'latitude'      => ['sometimes','nullable','numeric','between:-90,90'],
            'longitude'     => ['sometimes','nullable','numeric','between:-180,180'],
        ];
    }
}
