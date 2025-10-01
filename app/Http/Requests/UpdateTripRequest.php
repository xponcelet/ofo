<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTripRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Tu peux raffiner ça avec tes policies
        return true;
    }

    public function rules(): array
    {
        return [
            'title'          => 'required|string|max:100',
            'description'    => 'nullable|string',
            'image'          => 'nullable|string|max:2048',
            'budget'         => 'nullable|numeric|min:0',
            'currency'       => 'nullable|string|size:3',
            'is_public'      => 'boolean',

            // Champs liés à la Step destination
            'start_date'     => 'nullable|date',
            'end_date'       => 'nullable|date|after_or_equal:start_date',
            'nights'         => 'nullable|integer|min:0|max:365',
            'transport_mode' => 'nullable|string|in:car,plane,train,bus,bike,walk,boat',
        ];
    }

    public function messages(): array
    {
        return [
            'end_date.after_or_equal' => 'La date de fin doit être postérieure ou égale à la date de début.',
            'nights.min'              => 'Le nombre de nuits doit être au minimum 0.',
            'nights.max'              => 'Le nombre de nuits ne peut pas dépasser 365.',
            'transport_mode.in'       => 'Le mode de transport choisi est invalide.',
        ];
    }
}
