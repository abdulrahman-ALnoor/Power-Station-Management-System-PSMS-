<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceRequest extends FormRequest
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
            'consumption_charge_id' => ['required', 'exists:consumption_charges,id'],
            'paid_amount'                => ['required', 'numeric', 'min:0'],
            'payment_notes'              => ['nullable', 'string', 'max:1000'],
        ];
    }
    public function messages(): array
    {
        return [
            'consumption_charge_id.required' => 'معرف رسوم الاستهلاك مطلوب.',
            'consumption_charge_id.exists' => 'رسوم الاستهلاك غير موجودة.',

            'paid_amount.required' => 'المبلغ المدفوع مطلوب.',
            'paid_amount.numeric' => 'المبلغ المدفوع يجب أن يكون رقمًا.',
            'paid_amount.min' => 'المبلغ المدفوع يجب أن يكون أكبر من أو يساوي 0.',

            'payment_notes.string' => 'ملاحظات الدفع يجب أن تكون نصًا.',
            'payment_notes.max' => 'ملاحظات الدفع يجب ألا تتجاوز 1000 حرف.',
        ];
    }
}
