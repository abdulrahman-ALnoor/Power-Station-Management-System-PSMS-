<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // معرّف المستخدم الحالي (من الراوت) — نستثنيه من قاعدة unique على الإيميل
        $userId = $this->route('user');

        return [
            'name'     => 'sometimes|required|string|max:150',
            'email'    => 'sometimes|required|string|email|max:150|unique:users,email,' . $userId,
            'password' => 'nullable|string|min:8',
            'phone'    => 'nullable|string|max:30',
            'status'   => 'nullable|in:active,inactive',
            'role'     => 'sometimes|required|string|exists:roles,name',
        ];
    }

    /**
     * تخصيص استجابة أخطاء الـ Validation لتكون JSON موحد
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'خطأ في البيانات المدخلة',
            'errors'  => $validator->errors()
        ], 422));
    }
}
