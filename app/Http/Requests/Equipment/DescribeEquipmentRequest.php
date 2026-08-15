<?php

namespace App\Http\Requests\Equipment;

use Illuminate\Foundation\Http\FormRequest;

class DescribeEquipmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * التحقق الفعلي (مين يقدر يوصف أي معدة) يصير بالـ EquipmentPolicy@describe
     * جوه الكنترولر، مو هنا.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * حسب المستند: هذا تحديث محدود جداً — إضافة/تعديل الوصف (notes) بس.
     * ما نسمح بتغيير باقي حقول المعدة (الاسم، الرقم التسلسلي، الحالة، المالك...)
     * من هالمسار، حتى لو المستخدم بعتها بالـ request.
     */
    public function rules(): array
    {
        return [
            'notes' => [
                'required',
                'string',
            ],
        ];
    }

    /**
     * Validation messages.
     */
    public function messages(): array
    {
        return [
            'notes.required' => 'الوصف مطلوب.',
            'notes.string' => 'الوصف يجب أن يكون نصاً.',
        ];
    }
}
