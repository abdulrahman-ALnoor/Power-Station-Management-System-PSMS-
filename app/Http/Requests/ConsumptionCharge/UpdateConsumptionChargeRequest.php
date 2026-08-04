<?php

namespace App\Http\Requests\ConsumptionCharge;

use Illuminate\Foundation\Http\FormRequest;

class UpdateConsumptionChargeRequest extends FormRequest
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
            'customer_id' => ['sometimes', 'exists:customers,id'],
            'meter_id' => ['sometimes', 'exists:meters,id'],
            'meter_reading_id' => ['sometimes', 'exists:meter_readings,id'],

            'total_amount' => ['sometimes', 'numeric', 'min:0'],
            'paid_amount' => ['sometimes', 'numeric', 'min:0'],
            'remaining_amount' => ['sometimes', 'numeric', 'min:0'],

            'status' => [
                'sometimes',
                'in:pending,partially_paid,paid'
            ],
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'customer_id.exists' => 'العميل غير موجود.',

            'meter_id.exists' => 'العداد غير موجود.',

            'meter_reading_id.exists' => 'قراءة العداد غير موجودة.',

            'total_amount.numeric' => 'إجمالي المبلغ يجب أن يكون رقمًا.',
            'total_amount.min' => 'إجمالي المبلغ يجب أن يكون أكبر من أو يساوي صفر.',

            'paid_amount.numeric' => 'المبلغ المدفوع يجب أن يكون رقمًا.',
            'paid_amount.min' => 'المبلغ المدفوع يجب أن يكون أكبر من أو يساوي صفر.',

            'remaining_amount.numeric' => 'المبلغ المتبقي يجب أن يكون رقمًا.',
            'remaining_amount.min' => 'المبلغ المتبقي يجب أن يكون أكبر من أو يساوي صفر.',

            'status.in' => 'الحالة غير صحيحة.',
        ];
    }
}