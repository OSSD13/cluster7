<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class MinorCaseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // We'll handle authorization in the controller
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'board_id' => 'required|string',
            'sprint' => 'required|string',
            'card' => 'required|string',
            'description' => 'nullable|string',
            'member' => 'required|string',
            'points' => 'required|numeric|min:0',
        ];
    }
} 