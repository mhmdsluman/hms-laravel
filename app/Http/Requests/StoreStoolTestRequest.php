<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStoolTestRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->hasRole('lab_technician');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'order_item_id' => ['required', 'exists:order_items,id'],
            'results' => ['required', 'array'],
            'results.*.service_id' => ['required', 'exists:services,id'],
            'results.*.result' => ['required', 'string', 'max:255'],
        ];
    }
}
