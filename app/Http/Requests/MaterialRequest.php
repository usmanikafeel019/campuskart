<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MaterialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'student';
    }

    public function rules(): array
    {
        return ['title' => 'required|string|max:150', 'description' => 'required|string|min:10|max:5000', 'category_id' => 'required|exists:categories,id', 'subject_id' => 'required|exists:subjects,id', 'semester' => 'required|integer|between:1,8', 'price' => 'required|numeric|decimal:0,2|min:0|max:99999999.99', 'condition' => 'required|in:new,like_new,good,fair', 'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048', 'permission' => 'accepted'];
    }
}
