<?php

namespace App\Http\Requests\Production;

use Illuminate\Foundation\Http\FormRequest;

class InputActualOutputRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'daily_output_id' => ['required', 'exists:schedule_daily_outputs,id'],
            'actual_output' => ['required', 'integer', 'min:0'],
        ];
    }
}
