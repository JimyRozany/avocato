<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreCaseRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
   public function rules()
{
    return [
        'case_number' => 'required|unique:cases,case_number',
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'type' => 'nullable|string',
        'court_name' => 'nullable|string',
        'start_date' => 'nullable|date',
    ];
}
}
