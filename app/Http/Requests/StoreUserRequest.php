<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'     => 'required|string|max:150',
            'email'    => 'required|string|email|max:150|unique:users,email',
            'password' => 'required|string|min:8',
            'phone'    => 'nullable|string|max:30',
            'status'   => 'nullable|in:active,inactive',
            'role'     => 'required|string|exists:roles,name',
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
