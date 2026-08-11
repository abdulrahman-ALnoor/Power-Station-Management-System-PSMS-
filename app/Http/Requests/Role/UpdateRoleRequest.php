<?php

namespace App\Http\Requests\Role;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;


class UpdateRoleRequest extends FormRequest
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
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('roles', 'name')->ignore($this->route('role')->id),
            ],

            'description' => [
                'nullable',
                'string',
                'max:500',
            ],
        ];
    }


    public function messages(): array
    {
        return [

            'name.required' => 'اسم الدور مطلوب.',

            'name.unique' => 'اسم الدور موجود مسبقاً.',

            'name.max' => 'يجب ألا يتجاوز اسم الدور 100 حرف.',

            'description.max' => 'الوصف لا يمكن أن يتجاوز 500 حرف.',
        ];
    }
}
