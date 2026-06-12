<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $user = $this->user();

        $rules = [
            'case_number'  => 'required|unique:cases,case_number',
            'title'        => 'required|string|max:255',
            'description'  => 'nullable|string',
            'type'         => 'nullable|string',
            'court_name'   => 'nullable|string',
            'start_date'   => 'nullable|date',
            'role_in_case' => 'nullable|string',
            'side'         => 'nullable|string',
            'documents'    => 'nullable|array',
            'documents.*'  => 'file|max:51200',
        ];

        if ($user->hasRole('admin')) {
            $rules['client_id'] = 'required|exists:users,id';
            $rules['lawyer_id'] = 'required|exists:users,id';
        } elseif ($user->hasRole('avocato')) {
            $rules['client_id'] = 'required|exists:users,id';
        } elseif ($user->hasRole('client')) {
            $rules['lawyer_id'] = 'required|exists:users,id';
        }

        return $rules;
    }
}
