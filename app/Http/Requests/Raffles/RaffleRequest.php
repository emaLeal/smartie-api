<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RaffleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
                'name' => 'required|string',
                'is_played' => 'sometimes|boolean',
                'price' => 'required|string',
                'price_photo_url' => 'nullable|image|max:2048',
                'has_questions' => 'nullable|bool',
                'winner_id' => 'nullable|integer|exists:participants,id',
                'winner_name' => 'nullable|string',
                'events_id' => 'required|integer|exists:events,id'
        ];
    }
}
