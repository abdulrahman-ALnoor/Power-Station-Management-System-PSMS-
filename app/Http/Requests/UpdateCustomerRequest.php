<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // ⚠️ تغيير هذه إلى true
    }

    public function rules(): array
    {
        $id = $this->route('customer') ?? $this->route('id');

        return [
            'customer_number'     => 'nullable|string|max:50|unique:customers,customer_number,' . $id,
            'full_name'           => 'sometimes|string|max:150',
            'customer_type'       => 'sometimes|in:residential,commercial,industrial',
            'phone'               => 'sometimes|string|max:30',
            'alternative_phone'   => 'nullable|string|max:30',
            'address_description' => 'nullable|string',
            'notes'               => 'nullable|string',
        ];
    }
}