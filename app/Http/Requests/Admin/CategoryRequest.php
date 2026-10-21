<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $categoryId = $this->route('category') ? $this->route('category')->id : null;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name')->ignore($categoryId),
            ],
            // AÑADE ESTA REGLA PARA QUE EL SLUG SEA RECONOCIDO EN EL VALIDATED 👇
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'slug')->ignore($categoryId),
            ],
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('categories', 'id')->whereNull('parent_id'), // Solo dos niveles
                Rule::notIn(array_filter([$categoryId])),               // No puede ser su propio padre
            ],
            'is_active' => [
                'nullable',
                'boolean'
            ]
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->has('name')) {
            $this->merge([
                'slug' => \Str::slug($this->name),
                'is_active' => $this->has('is_active') ? true : false,
            ]);
        }
    }
}