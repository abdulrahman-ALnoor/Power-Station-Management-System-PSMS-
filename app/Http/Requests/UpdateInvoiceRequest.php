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
    public function messages(): array
    {
        return [
            'outstanding_before_payment.numeric' => 'المبلغ المستحق قبل الدفع يجب أن يكون رقمًا.',
            'outstanding_before_payment.min' => 'المبلغ المستحق قبل الدفع يجب أن يكون أكبر من أو يساوي 0.',

            'paid_amount.numeric' => 'المبلغ المدفوع يجب أن يكون رقمًا.',
            'paid_amount.min' => 'المبلغ المدفوع يجب أن يكون أكبر من أو يساوي 0.',

            'remaining_balance.numeric' => 'الرصيد المتبقي يجب أن يكون رقمًا.',
            'remaining_balance.min' => 'الرصيد المتبقي يجب أن يكون أكبر من أو يساوي 0.',

            'status.in' => 'الحالة يجب أن تكون إما "paid" أو "partially_paid".',

            'payment_notes.string' => 'ملاحظات الدفع يجب أن تكون نصًا.',
            'payment_notes.max' => 'ملاحظات الدفع يجب ألا تتجاوز 1000 حرف.',
        ];
    }
}
