<?php

namespace App\Http\Requests;

use App\Enums\RecurrenceType;
use App\Models\Category;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'city_planner';
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'event_type' => ['required', 'string', 'max:255'],
            'event_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'recurrence_type' => ['required', Rule::enum(RecurrenceType::class)],

            'scores' => ['nullable', 'array'],
            'scores.*' => ['nullable', 'integer', 'min:-5', 'max:5'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $categoryIds = Category::pluck('id')
                    ->map(fn ($id) => (string) $id)
                    ->toArray();

                $scoreIds = array_keys($this->input('scores', []));

                $missingCategoryScores = array_diff($categoryIds, $scoreIds);

                if (! empty($missingCategoryScores)) {
                    $validator->errors()->add(
                        'scores',
                        'Every category must have a score.'
                    );
                }
            },
        ];
    }
}