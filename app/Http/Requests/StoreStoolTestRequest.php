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
            'patient_id' => ['required_without:order_id', 'exists:patients,id'],
            'order_id' => ['required_without:patient_id', 'exists:orders,id'],
            'results' => ['required', 'array'],
            'results.*.id' => ['sometimes', 'exists:lab_results,id'],
            'results.*.order_item_id' => ['required_without:results.*.id', 'exists:order_items,id'],
            'results.*.value' => ['required', 'string', 'max:255'],
        ];
    }
}
