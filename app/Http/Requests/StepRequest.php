<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StepRequest extends FormRequest
{
    public function authorize(): bool
    {
        // La Policy s'applique déjà dans le controller (authorize('update', ...))
        return true;
    }

    public function rules(): array
    {
        return [
            'title'          => 'nullable|string|max:100',
            'description'    => 'nullable|string',
            'location'       => 'required|string',
            'latitude'       => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'      => ['nullable', 'numeric', 'between:-180,180'],
            'start_date'     => 'nullable|date',
            'nights'         => 'nullable|integer|min:0',
            'transport_mode' => 'nullable|string|in:car,plane,train,bus,bike,walk,boat',
            'country'        => 'nullable|string|max:100',

            // insertion
            'insert_after_id' => 'nullable|integer|exists:steps,id',
            'at_start'        => 'nullable|boolean',
        ];
    }
}
