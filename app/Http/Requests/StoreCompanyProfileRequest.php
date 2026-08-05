<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
}