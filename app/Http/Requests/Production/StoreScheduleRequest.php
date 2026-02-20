<?php

namespace App\Http\Requests\Production;

use Illuminate\Foundation\Http\FormRequest;

class StoreScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_id' => ['required', 'exists:master_orders,id'],
            'line_id' => ['required', 'exists:master_lines,id'],
            'start_date' => ['required', 'date'],
            'finish_date' => ['required', 'date', 'after_or_equal:start_date'],
            'qty_total_target' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'finish_date.after_or_equal' => 'Finish date must be equal to or after start date.',
        ];
    }
}
