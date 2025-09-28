<?php

namespace App\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;
use App\Models\Trip;
class StoreTripRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Trip::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'budget' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            'is_public' => 'boolean',
        ];
    }

}
