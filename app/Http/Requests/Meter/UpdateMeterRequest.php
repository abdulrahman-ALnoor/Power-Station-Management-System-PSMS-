<?php

namespace App\Http\Requests\Meter;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMeterRequest extends FormRequest
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

            'meter_number' => [
                'sometimes',
                'string',
                'max:100',
                Rule::unique('meters', 'meter_number')->ignore($this->route('meter')),
            ],

            'qr_code' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('meters', 'qr_code')->ignore($this->route('meter')),
            ],

            'installation_date' => ['nullable', 'date'],
            'installation_location' => ['nullable', 'string'],
            'status' => ['sometimes', 'in:active,disconnected,maintenance,damaged'],
            'installed_by' => ['sometimes', 'exists:users,id'],
            'created_by' => ['nullable', 'exists:users,id'],
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'customer_id.exists' => 'العميل غير موجود.',

            'meter_number.string' => 'رقم العداد يجب أن يكون نصًا.',
            'meter_number.max' => 'رقم العداد يجب ألا يتجاوز 100 حرف.',
            'meter_number.unique' => 'رقم العداد مستخدم مسبقًا.',

            'qr_code.string' => 'رمز QR يجب أن يكون نصًا.',
            'qr_code.max' => 'رمز QR يجب ألا يتجاوز 255 حرفًا.',
            'qr_code.unique' => 'رمز QR مستخدم مسبقًا.',

            'installation_date.date' => 'تاريخ التركيب غير صحيح.',

            'installation_location.string' => 'موقع التركيب يجب أن يكون نصًا.',

            'status.in' => 'حالة العداد غير صحيحة.',

            'installed_by.exists' => 'الفني المسؤول غير موجود.',

            'created_by.exists' => 'منشئ السجل غير موجود.',
        ];
    }
}