<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StepRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'          => ['nullable', 'string', 'max:255'],
            'description'    => ['nullable', 'string'],
            'location' => ['required', 'string', 'max:100'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'country' => ['nullable', 'string', 'max:100'],
            'country_code' => ['nullable', 'string', 'size:2'],
            'order' => ['nullable', 'integer', 'min:1'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'nights' => ['nullable', 'integer', 'min:0'],
            'transport_mode' => ['nullable', 'string', 'max:50'],
            'is_destination' => ['sometimes', 'boolean'],
            'is_departure' => ['sometimes', 'boolean'],
        ];
    }
}
