<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreActivityRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title'         => ['required','string','max:120'],
            'description'   => ['nullable','string','max:5000'],

            'location'      => ['nullable','string','max:255'],

            // ✅ On accepte date+time (et on reconstruira start_at côté serveur)
            'date'          => ['nullable','date'],
            'time'          => ['nullable','date_format:H:i'],

            // (on ignore un éventuel start_at envoyé par le front)
            'start_at'      => ['prohibited'],
            'end_at'        => ['nullable','date','after_or_equal:date'],

            // ✅ URL moins stricte : on normalise côté serveur
            'external_link' => ['nullable','string','max:255'],

            'cost'          => ['nullable','numeric','min:0'],
            'currency'      => ['nullable','string','size:3'],
            'category'      => ['nullable','string','max:100'],

            'latitude'      => ['nullable','numeric','between:-90,90'],
            'longitude'     => ['nullable','numeric','between:-180,180'],
        ];
    }
}
