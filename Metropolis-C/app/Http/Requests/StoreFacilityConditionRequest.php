<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFacilityConditionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'policy_maker';
    }

    public function rules(): array
    {
        $facility = $this->route('facility');
        $condition = $this->route('condition');

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
                Rule::unique('facility_conditions')
                    ->where(fn ($query) => $query
                        ->where('facility_id', $facility->id)
                        ->where('condition_type', $this->input('condition_type')))
                    ->ignore($condition?->id),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'neighbour_facility_id.not_in' => 'A facility cannot have itself as a neighbour condition.',
            'neighbour_facility_id.unique' => 'This condition already exists for the selected facility.',
        ];
    }
}
