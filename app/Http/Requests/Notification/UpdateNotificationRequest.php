<?php

namespace App\Http\Requests\Notification;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNotificationRequest extends FormRequest
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
            'customer_id' => [
                'required',
                'exists:customers,id',
            ],

            'meter_reading_id' => [
                'nullable',
                'exists:meter_readings,id',
            ],

            'invoice_id' => [
                'nullable',
                'exists:invoices,id',
            ],

            'notification_type' => [
                'required',
                'in:reading,payment,service_request,general',
            ],

            'message' => [
                'required',
                'string',
            ],

            'status' => [
                'nullable',
                'in:pending,sent,failed,read',
            ],

            'whatsapp_message_id' => [
                'nullable',
                'string',
                'max:255',
            ],

            'sent_at' => [
                'nullable',
                'date',
            ],

            'read_at' => [
                'nullable',
                'date',
            ],
        ];
    }

    
    //  Validation messages.
     
    public function messages(): array
    {
        return [
            'customer_id.required' => 'العميل مطلوب.',
            'customer_id.exists' => 'العميل المحدد غير موجود.',

            'meter_reading_id.exists' => 'قراءة العداد المحددة غير موجودة.',

            'invoice_id.exists' => 'الفاتورة المحددة غير موجودة.',

            'notification_type.required' => 'نوع الإشعار مطلوب.',
            'notification_type.in' => 'نوع الإشعار غير صالح.',

            'message.required' => 'نص الإشعار مطلوب.',
            'message.string' => 'نص الإشعار يجب أن يكون نصًا.',

            'status.in' => 'حالة الإشعار غير صحيحة.',

            'whatsapp_message_id.max' => 'لا يجب أن يتجاوز معرف رسالة واتساب 255 حرفًا.',

            'sent_at.date' => 'تاريخ الإرسال غير صحيح.',

            'read_at.date' => 'تاريخ القراءة غير صحيح.',
        ];
    }
}
