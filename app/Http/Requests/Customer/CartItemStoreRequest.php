<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class CartItemStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'exists:products,id'],
            'variant_id' => ['nullable', 'exists:product_variants,id'],
            'quantity' => ['required', 'numeric', 'min:0.001'],
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required' => 'المنتج مطلوب.',
            'product_id.exists' => 'المنتج غير موجود.',
            'variant_id.exists' => 'هذا الخيار غير متاح حالياً.',
            'quantity.required' => 'الكمية مطلوبة.',
            'quantity.numeric' => 'الكمية يجب أن تكون رقماً.',
            'quantity.min' => 'الكمية يجب أن تكون أكبر من صفر.',
        ];
    }
}
