<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreCaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

   public function rules()
{
    $user = $this->user();

    $rules = [
        'case_number' => 'required|unique:cases,case_number',
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'type' => 'nullable|string',
        'court_name' => 'nullable|string',
        'start_date' => 'nullable|date',
        'documents' => 'nullable|array',
        'documents.*' => 'file|max:2048',
    ];

    if ($user->hasRole('avocato')) {
        $rules['client_id'] = 'required|exists:users,id';
        $rules['role_in_case'] = 'nullable|string';
    } elseif ($user->hasRole('client')) {
        $rules['lawyer_id'] = 'required|exists:users,id';
        $rules['side'] = 'nullable|string';
    } else {
        $rules['parties'] = 'nullable|array';
        $rules['parties.*.user_id'] = 'required|exists:users,id';
        $rules['parties.*.role_in_case'] = 'required|string';
        $rules['lawyers'] = 'nullable|array';
        $rules['lawyers.*.lawyer_id'] = 'required|exists:users,id';
        $rules['lawyers.*.side'] = 'nullable|string';
    }

    return $rules;
}
}
