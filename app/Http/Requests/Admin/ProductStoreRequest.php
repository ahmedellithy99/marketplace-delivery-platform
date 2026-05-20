<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $type = $this->input('type', 'simple');

        $rules = [
            'store_id' => ['required', 'exists:stores,id'],
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', Rule::in(['simple', 'variant', 'measured'])],
            'is_available' => ['nullable', 'boolean'],
            'images' => ['nullable', 'array'],
            'images.*' => ['image', 'max:5120'],
        ];

        // Simple & Measured require base_price
        if (in_array($type, ['simple', 'measured'])) {
            $rules['base_price'] = ['required', 'numeric', 'min:0.01'];
        } else {
            $rules['base_price'] = ['nullable', 'numeric', 'min:0'];
        }

        // Measured requires measurement fields
        if ($type === 'measured') {
            $rules['measurement_unit'] = ['required', 'string', 'max:50'];
            $rules['min_quantity'] = ['nullable', 'numeric', 'min:0'];
            $rules['max_quantity'] = ['nullable', 'numeric', 'min:0'];
            $rules['quantity_step'] = ['nullable', 'numeric', 'min:0.001'];
        }

        // Variant requires at least one variant
        if ($type === 'variant') {
            $rules['variants'] = ['required', 'array', 'min:1'];
            $rules['variants.*.name'] = ['required', 'string', 'max:255'];
            $rules['variants.*.price'] = ['required', 'numeric', 'min:0.01'];
        }

        return $rules;
    }
}
