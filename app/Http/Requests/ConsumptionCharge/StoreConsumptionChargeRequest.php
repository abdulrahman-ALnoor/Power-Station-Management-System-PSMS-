<?php

namespace App\Http\Requests\ConsumptionCharge;

use Illuminate\Foundation\Http\FormRequest;

class StoreConsumptionChargeRequest extends FormRequest
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
            'customer_id' => ['required', 'exists:customers,id'],
            'meter_id' => ['required', 'exists:meters,id'],
            'meter_reading_id' => [
                'required',
                'exists:meter_readings,id',
                'unique:consumption_charges,meter_reading_id'
            ],

            'total_amount' => ['required', 'numeric', 'min:0'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'remaining_amount' => ['required', 'numeric', 'min:0'],

            'status' => [
                'required',
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
            'customer_id.required' => 'العميل مطلوب.',
            'customer_id.exists' => 'العميل غير موجود.',

            'meter_id.required' => 'العداد مطلوب.',
            'meter_id.exists' => 'العداد غير موجود.',

            'meter_reading_id.required' => 'قراءة العداد مطلوبة.',
            'meter_reading_id.exists' => 'قراءة العداد غير موجودة.',
            'meter_reading_id.unique' => 'تم إنشاء رسوم لهذه القراءة مسبقًا.',

            'total_amount.required' => 'إجمالي المبلغ مطلوب.',
            'total_amount.numeric' => 'إجمالي المبلغ يجب أن يكون رقمًا.',
            'total_amount.min' => 'إجمالي المبلغ يجب أن يكون أكبر من أو يساوي صفر.',

            'paid_amount.numeric' => 'المبلغ المدفوع يجب أن يكون رقمًا.',
            'paid_amount.min' => 'المبلغ المدفوع يجب أن يكون أكبر من أو يساوي صفر.',

            'remaining_amount.required' => 'المبلغ المتبقي مطلوب.',
            'remaining_amount.numeric' => 'المبلغ المتبقي يجب أن يكون رقمًا.',
            'remaining_amount.min' => 'المبلغ المتبقي يجب أن يكون أكبر من أو يساوي صفر.',

            'status.required' => 'الحالة مطلوبة.',
            'status.in' => 'الحالة غير صحيحة.',
        ];
    }
}