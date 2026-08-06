<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator; 
use Illuminate\Http\Exceptions\HttpResponseException; 

class StoreCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_number'     => 'nullable|string|max:50|unique:customers,customer_number',
            'full_name'           => 'required|string|max:150',
            'customer_type'       => 'required|in:residential,commercial,industrial',
            'phone'               => 'required|string|max:30',
            'alternative_phone'   => 'nullable|string|max:30',
            'address_description' => 'nullable|string',
            'notes'               => 'nullable|string',
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