<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMeterReadingRequest extends FormRequest
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

            'current_reading' => [
                'required',
                'numeric',
                'min:0',
            ],

            'reading_date' => [
                'required',
                'date',
            ],

            'reading_method' => [
                'nullable',
                'in:manual,qr_scan',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:1000',
            ],

            // كانت status مفقودة من هذا الملف بالكامل رغم إن الكنترولر يعتمد عليها
            // ($validated['status'] ?? ...) لاعتماد/رفض القراءة — يعني عملياً ما
            // كان فيه طريقة يغيّر فيها الأدمن حالة القراءة عبر endpoint التحديث.
            'status' => [
                'sometimes',
                'in:pending,approved,rejected',
            ],
        ];
    }
    public function messages(): array
    {
        return [

            'current_reading.required' => 'القراءة الحالية مطلوبة.',
            'current_reading.numeric' => 'القراءة الحالية يجب أن تكون رقمية.',
            'current_reading.min' => 'القراءة الحالية يجب أن تكون أكبر من أو تساوي 0.',

            'reading_date.required' => 'تاريخ القراءة مطلوب.',
            'reading_date.date' => 'تاريخ القراءة يجب أن يكون تاريخًا صالحًا.',

            'reading_method.in' => 'طريقة القراءة يجب أن تكون إما "manual" أو "qr_scan".',

            'notes.string' => 'الملاحظات يجب أن تكون نصًا.',
            'notes.max' => 'الملاحظات يجب ألا تتجاوز 1000 حرف.',

            'status.in' => 'حالة القراءة غير صحيحة.',
        ];
    }
}
