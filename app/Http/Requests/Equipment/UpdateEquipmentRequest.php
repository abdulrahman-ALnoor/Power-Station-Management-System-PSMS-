<?php

namespace App\Http\Requests\Equipment;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateEquipmentRequest extends FormRequest
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
            'user_id' => [
                'nullable',
                'exists:users,id',
            ],

            'equipment_name' => [
                'required',
                'string',
                'max:150',
            ],

            'serial_number' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('equipment', 'serial_number')
                    ->ignore($this->equipment),
            ],

            'status' => [
                'nullable',
                'in:available,maintenance,damaged,lost',
            ],

            'notes' => [
                'nullable',
                'string',
            ],

            'created_by' => [
                'nullable',
                'exists:users,id',
            ],
        ];
    }

    /**
     * Validation messages.
     */
    public function messages(): array
    {
        return [
            'user_id.exists' => 'المستخدم المحدد غير موجود.',

            'equipment_name.required' => 'اسم الجهاز مطلوب.',
            'equipment_name.max' => 'يجب ألا يتجاوز اسم الجهاز 150 حرفاً.',

            'serial_number.unique' => 'الرقم التسلسلي مستخدم مسبقاً.',
            'serial_number.max' => 'يجب ألا يتجاوز الرقم التسلسلي 100 حرف.',

            'status.in' => 'حالة الجهاز غير صحيحة.',

            'notes.string' => 'الملاحظات يجب أن تكون نصاً.',

            'created_by.exists' => 'المستخدم المنشئ غير موجود.',
        ];
    }
}
