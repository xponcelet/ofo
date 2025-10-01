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
            'description'   => ['nullable','string'],
            'location' => ['nullable', 'string', 'max:255'],
            'start_at'      => ['nullable','date'],
            'end_at'        => ['nullable','date','after_or_equal:start_at'],
            'external_link' => ['nullable','url'],
            'cost'          => ['nullable','numeric','min:0'],
            'currency'      => ['nullable','string','size:3'],
            'category'      => ['nullable','string','max:50'],
        ];
    }
}
