<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInvoiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'outstanding_before_payment' => ['sometimes', 'numeric', 'min:0'],
            'paid_amount'                => ['sometimes', 'numeric', 'min:0'],
            'remaining_balance'          => ['sometimes', 'numeric', 'min:0'],
            'status'                     => ['sometimes', 'in:paid,partially_paid'],
            'payment_notes'              => ['nullable', 'string', 'max:1000'],
        ];
    }
}
