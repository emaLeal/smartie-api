<?php

namespace App\Http\Requests;

class UpdateEventRequest extends EventRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return array_map(fn($rule) => "sometimes|$rule", parent::rules());
    }
}
