<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFacilityConditionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'library_manager'
            || $this->user()?->role === 'admin';
    }

    public function rules(): array
    {
        $facility = $this->route('facility');

        return [
            'condition_type' => [
                'required',
                Rule::in([
                    'required_neighbour',
                    'forbidden_neighbour',
                ]),
            ],

            'neighbour_facility_id' => [
                'required',
                'exists:facilities,id',
                Rule::notIn([$facility->id]),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'neighbour_facility_id.not_in' => 'A facility cannot have itself as a neighbour condition.',
        ];
    }
}
