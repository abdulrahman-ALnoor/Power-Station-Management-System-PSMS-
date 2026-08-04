<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMeterReadingRequest extends FormRequest
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
            'meter_id' => [
                'required',
                'exists:meters,id',
            ],

            

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
        ];
    }
    public function messages(): array
    {
        return [

            'meter_id.required' => 'العداد مطلوب.',
            'meter_id.exists' => 'العداد غير موجود.',

            'previous_reading.required' => 'القراءة السابقة مطلوبة.',
            'previous_reading.numeric' => 'القراءة السابقة يجب أن تكون رقمًا.',

            'current_reading.required' => 'القراءة الحالية مطلوبة.',
            'current_reading.numeric' => 'القراءة الحالية يجب أن تكون رقمًا.',
            'current_reading.gte' => 'يجب أن تكون القراءة الحالية أكبر من أو تساوي القراءة السابقة.',

            'reading_date.required' => 'تاريخ القراءة مطلوب.',
            'reading_date.date' => 'تاريخ القراءة غير صحيح.',

            'reading_method.in' => 'طريقة القراءة غير صحيحة.',

            'notes.max' => 'الملاحظات يجب ألا تتجاوز 1000 حرف.',

        ];
    }
}
