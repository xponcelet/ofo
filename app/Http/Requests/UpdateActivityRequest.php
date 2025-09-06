<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateActivityRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $activity = $this->route('activity');
        $tripId   = $activity->step->trip_id;

        return [
            'step_id'       => ['required','integer', Rule::exists('steps','id')->where('trip_id',$tripId)],
            'title'         => ['required','string','max:120'],
            'description'   => ['nullable','string'],
            'start_at'      => ['nullable','date'],
            'end_at'        => ['nullable','date','after_or_equal:start_at'],
            'external_link' => ['nullable','url'],
            'cost'          => ['nullable','numeric','min:0'],
            'currency'      => ['nullable','string','size:3'],
            'category'      => ['nullable','string','max:50'],
        ];
    }
}
