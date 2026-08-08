<?php

namespace App\Http\Requests\Meter;

use Illuminate\Foundation\Http\FormRequest;

class StoreMeterRequest extends FormRequest
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
            'meter_number' => ['required', 'string', 'max:100', 'unique:meters,meter_number'],
            'qr_code' => ['required', 'string', 'max:255', 'unique:meters,qr_code'],
            'installation_date' => ['nullable', 'date'],
            'installation_location' => ['nullable', 'string'],
            'status' => ['nullable', 'in:active,disconnected,maintenance,damaged'],
            'installed_by' => ['required', 'exists:users,id'],
            'created_by' => ['nullable', 'exists:users,id'],
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

            'meter_number.required' => 'رقم العداد مطلوب.',
            'meter_number.unique' => 'رقم العداد مستخدم مسبقًا.',
            'meter_number.max' => 'رقم العداد يجب ألا يتجاوز 100 حرف.',

            'qr_code.required' => 'رمز QR مطلوب.',
            'qr_code.unique' => 'رمز QR مستخدم مسبقًا.',
            'qr_code.max' => 'رمز QR يجب ألا يتجاوز 255 حرفًا.',

            'installation_date.date' => 'تاريخ التركيب غير صحيح.',

            'installation_location.string' => 'موقع التركيب يجب أن يكون نصًا.',

            'status.in' => 'حالة العداد غير صحيحة.',

            'installed_by.required' => 'الفني المسؤول مطلوب.',
            'installed_by.exists' => 'الفني المسؤول غير موجود.',

            'created_by.exists' => 'منشئ السجل غير موجود.',
        ];
    }
}