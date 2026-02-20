<?php

namespace App\Http\Requests\Production;

use App\Models\Line;
use App\Models\Order;
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

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Check line availability in the date range
            if ($this->filled(['line_id', 'start_date', 'finish_date'])) {
                $line = Line::find($this->input('line_id'));
                if ($line && ! $line->isAvailableInRange($this->input('start_date'), $this->input('finish_date'))) {
                    $validator->errors()->add('line_id', 'This production line is already scheduled in the selected date range.');
                }
            }

            // Check if qty_total_target exceeds order remaining qty
            if ($this->filled(['order_id', 'qty_total_target'])) {
                $order = Order::find($this->input('order_id'));
                if ($order) {
                    $remainingQty = $order->remaining_qty ?? $order->qty_total;
                    if ($this->input('qty_total_target') > $remainingQty) {
                        $validator->errors()->add('qty_total_target', "Target quantity ({$this->input('qty_total_target')}) exceeds order remaining quantity ({$remainingQty}).");
                    }
                }
            }
        });
    }
}
