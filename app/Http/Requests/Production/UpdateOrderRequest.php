<?php

namespace App\Http\Requests\Production;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $orderId = $this->route('order');

        return [
            'order_number' => ['required', 'string', 'max:255', Rule::unique('master_orders', 'order_number')->ignore($orderId)],
            'product_name' => ['required', 'string', 'max:255'],
            'product_code' => ['nullable', 'string', 'max:100'],
            'qty_total' => ['required', 'integer', 'min:1'],
            'customer' => ['nullable', 'string', 'max:255'],
            'order_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:order_date'],
            'status' => ['nullable', 'in:pending,scheduled,in_progress,completed,cancelled'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
