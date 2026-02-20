<?php

namespace App\Http\Requests\Production;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_number' => ['required', 'string', 'max:255', 'unique:master_orders,order_number'],
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
