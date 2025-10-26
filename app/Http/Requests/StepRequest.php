<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
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
            'location'       => ['required', 'string', 'max:100'],
            'latitude'       => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'      => ['nullable', 'numeric', 'between:-180,180'],
            'country'        => ['nullable', 'string', 'max:100'],
            'country_code'   => ['nullable', 'string', 'size:2'],
            'order'          => ['nullable', 'integer', 'min:1'],
            'start_date'     => ['nullable', 'date', 'after_or_equal:today'],
            'end_date'       => ['nullable', 'date', 'after_or_equal:start_date'],
            'nights'         => ['nullable', 'integer', 'min:0'],
            'transport_mode' => ['nullable', 'string', 'max:50'],
            'is_destination' => ['sometimes', 'boolean'],
            'is_departure'   => ['sometimes', 'boolean'],
        ];
    }

    /**
     * Normalisations légères avant validation (coercions/cleanup).
     */
    protected function prepareForValidation(): void
    {
        $data = $this->all();

        // Trim strings basiques
        foreach (['title','description','location','country'] as $k) {
            if (isset($data[$k]) && is_string($data[$k])) {
                $data[$k] = trim($data[$k]);
            }
        }

        // Uppercase du country_code (et tronqué à 2 par sécurité)
        if (isset($data['country_code']) && is_string($data['country_code'])) {
            $data['country_code'] = strtoupper(substr(trim($data['country_code']), 0, 2));
        }

        // Coercion nights -> int >= 0 (si fourni)
        if ($this->has('nights')) {
            $n = (int) $this->input('nights');
            if ($n < 0) $n = 0;
            $data['nights'] = $n;
        }

        // Transport_mode en minuscule (si fourni)
        if (isset($data['transport_mode']) && is_string($data['transport_mode'])) {
            $data['transport_mode'] = strtolower(trim($data['transport_mode']));
        }

        $this->replace($data);
    }

    public function messages(): array
    {
        return [
            'location.required'        => 'Le lieu est requis.',
            'start_date.after_or_equal'=> 'La date de début ne peut pas être dans le passé.',
            'end_date.after_or_equal'  => 'La date de fin doit être postérieure ou égale à la date de début.',
            'nights.min'               => 'Le nombre de nuits doit être supérieur ou égal à 0.',
            'transport_mode.in'        => 'Le moyen de transport sélectionné n’est pas valide.',
        ];
    }

    public function attributes(): array
    {
        return [
            'title'          => 'titre',
            'description'    => 'description',
            'location'       => 'lieu',
            'start_date'     => 'date de début',
            'end_date'       => 'date de fin',
            'nights'         => 'nuits',
            'country'        => 'pays',
            'country_code'   => 'code pays',
            'transport_mode' => 'moyen de transport',
        ];
    }
}
