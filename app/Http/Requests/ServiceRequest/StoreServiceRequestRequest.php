<?php

namespace App\Http\Requests\ServiceRequest;

use Illuminate\Foundation\Http\FormRequest;

class StoreServiceRequestRequest extends FormRequest
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

            'customer_id' => [
                'required',
                'exists:customers,id',
            ],

            'created_by' => [
                'required',
                'exists:users,id',
            ],

            'assigned_engineer_id' => [
                'nullable',
                'exists:users,id',
            ],

            'request_type' => [
                'required',
                'in:new_connection,maintenance,disconnection',
            ],

            'priority' => [
                'nullable',
                'in:low,medium,high,emergency',
            ],

            'status' => [
                'nullable',
                'in:pending,assigned,in_progress,completed,cancelled',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'completed_at' => [
                'nullable',
                'date',
            ],
        ];
    }

    /**
     * Validation messages.
     */
    public function messages(): array
    {
        return [
            'meter_id.required' => 'العداد مطلوب.',
            'meter_id.exists' => 'العداد المحدد غير موجود.',

            'customer_id.required' => 'العميل مطلوب.',
            'customer_id.exists' => 'العميل المحدد غير موجود.',

            'created_by.required' => 'منشئ الطلب مطلوب.',
            'created_by.exists' => 'المستخدم المحدد غير موجود.',

            'assigned_engineer_id.exists' => 'المهندس المحدد غير موجود.',

            'request_type.required' => 'نوع الطلب مطلوب.',
            'request_type.in' => 'نوع الطلب غير صحيح.',

            'priority.in' => 'الأولوية غير صحيحة.',

            'status.in' => 'حالة الطلب غير صحيحة.',

            'description.string' => 'الوصف يجب أن يكون نصًا.',

            'completed_at.date' => 'تاريخ الإنجاز غير صحيح.',
        ];
    }
}
