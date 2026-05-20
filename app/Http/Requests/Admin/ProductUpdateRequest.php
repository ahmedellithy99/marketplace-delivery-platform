<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $type = $this->input('type', $this->route('product')?->type ?? 'simple');

        $rules = [
            'store_id' => ['sometimes', 'exists:stores,id'],
            'category_id' => ['sometimes', 'exists:categories,id'],
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['sometimes', Rule::in(['simple', 'variant', 'measured'])],
            'images' => ['nullable', 'array'],
            'images.*' => ['image', 'max:5120'],
        ];

        if (in_array($type, ['simple', 'measured'])) {
            $rules['base_price'] = ['sometimes', 'numeric', 'min:0.01'];
        }

        if ($type === 'measured') {
            $rules['measurement_unit'] = ['sometimes', 'string', 'max:50'];
            $rules['min_quantity'] = ['nullable', 'numeric', 'min:0'];
            $rules['max_quantity'] = ['nullable', 'numeric', 'min:0'];
            $rules['quantity_step'] = ['nullable', 'numeric', 'min:0.001'];
        }

        return $rules;
    }
}
