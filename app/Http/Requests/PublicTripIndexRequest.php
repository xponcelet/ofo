<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PublicTripIndexRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'q'     => ['nullable','string','max:100'],
            'sort'  => ['nullable', Rule::in(['latest','oldest','title'])],
            'page'  => ['nullable','integer','min:1'],
            'per'   => ['nullable','integer','min:1','max:50'], // facultatif
        ];
    }

    public function perPage(): int
    {
        return (int) ($this->input('per') ?: 12);
    }
}
