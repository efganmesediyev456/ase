<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class TrackUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'package_id' => ['required'],
            'delivery_number' => ['required'],
            'weight' => ['required', 'numeric', 'min:0.001']
        ];
    }

    public function messages(): array
    {
        return [
            'package_id.required' => 'Please include the Package ID.',
            'weight.required' => 'Please specify the weight of the package.',
            'weight.numeric' => 'The weight must be a number.',
            'weight.min' => 'The weight must be greater than 0 kg.',
            'delivery_number.required' => 'Please enter the Delivery Number.'
        ];
    }
}
