<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreTypeStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:store_types,name'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'اسم النوع مطلوب.',
            'name.unique' => 'اسم النوع موجود بالفعل.',
            'name.max' => 'اسم النوع يجب ألا يتجاوز 255 حرف.',
        ];
    }
}
