<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUrineTestRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->hasRole('lab_technician');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'patient_id' => ['required', 'exists:patients,id'],
            'values.color' => ['nullable', 'string', 'max:255'],
            'values.appearance' => ['nullable', 'string', 'max:255'],
            'values.specific_gravity' => ['nullable', 'numeric', 'min:1.000', 'max:1.050'],
            'values.ph' => ['nullable', 'numeric', 'min:4.0', 'max:9.0'],
            'values.protein' => ['nullable', 'string', 'max:255'],
            'values.glucose' => ['nullable', 'string', 'max:255'],
            'values.ketones' => ['nullable', 'string', 'max:255'],
            'values.blood' => ['nullable', 'string', 'max:255'],
            'values.bilirubin' => ['nullable', 'string', 'max:255'],
            'values.urobilinogen' => ['nullable', 'string', 'max:255'],
            'values.nitrite' => ['nullable', 'string', 'max:255'],
            'values.leukocyte_esterase' => ['nullable', 'string', 'max:255'],
            'values.rbcs' => ['nullable', 'numeric', 'min:0'],
            'values.wbcs' => ['nullable', 'numeric', 'min:0'],
            'values.epithelial_cells' => ['nullable', 'numeric', 'min:0'],
            'values.casts' => ['nullable', 'string', 'max:255'],
            'values.crystals' => ['nullable', 'string', 'max:255'],
            'values.bacteria' => ['nullable', 'string', 'max:255'],
            'values.yeast' => ['nullable', 'string', 'max:255'],
            'values.mucus' => ['nullable', 'string', 'max:255'],
        ];
    }
}
