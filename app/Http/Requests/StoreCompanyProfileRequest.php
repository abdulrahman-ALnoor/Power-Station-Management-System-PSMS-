<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreCompanyProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company_name'       => 'required|string|max:200',
            'logo'               => 'nullable|string|max:255',
            'address'            => 'nullable|string',
            'whatsapp_number'    => 'nullable|string|max:30',
            'support_number'     => 'nullable|string|max:30',
            'currency'           => 'required|string|max:20',
            'price_per_kwh'      => 'required|numeric|min:0',
            'reading_cycle_days' => 'nullable|integer|min:1',
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