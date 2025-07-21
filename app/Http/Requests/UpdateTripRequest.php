<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTripRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Tu peux ajuster ça selon les permissions ou politiques
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:100',
            'description' => 'nullable|string',
            'image' => 'nullable|string|max:2048',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'budget' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            'is_public' => 'boolean',
        ];
    }

}
