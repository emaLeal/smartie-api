<?php

namespace App\Http\Requests;

class CreateEventRequest extends EventRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'name' => 'required|' . parent::rules()['name'],
            'organization' => 'required|' . parent::rules()['organization'],
        ]);
    }
}
