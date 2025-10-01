<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTripRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'title'          => ['required', 'string', 'max:255'],
            'description'    => ['nullable', 'string'],
            'image'          => ['nullable', 'url', 'max:2048'],

            // Dates et nuits
            'start_date'     => ['required', 'date', 'after_or_equal:today'],
            'end_date'       => ['nullable', 'date', 'after_or_equal:start_date'],
            'nights'         => ['nullable', 'integer', 'min:0'],

            // Logistique
            'transport_mode' => ['nullable', 'string', 'in:car,plane,train,bus,bike,walk,boat'],
            'budget'         => ['nullable', 'numeric', 'min:0'],
            'currency'       => ['nullable', 'string', 'in:EUR,USD'],
            'is_public'      => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'start_date.required' => 'La date de départ est obligatoire.',
            'start_date.after_or_equal' => 'La date de départ doit être aujourd’hui ou plus tard.',
            'end_date.after_or_equal' => 'La date de fin doit être après ou égale à la date de départ.',
            'nights.min' => 'Le nombre de nuits ne peut pas être négatif.',
        ];
    }
}
