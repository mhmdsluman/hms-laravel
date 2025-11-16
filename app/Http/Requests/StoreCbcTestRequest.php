<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCbcTestRequest extends FormRequest
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
            'patient_id' => ['required','exists:patients,id'],
            'values.wbc' => ['nullable','numeric','min:0','max:1000'],
            'values.neutrophils_pct' => ['nullable','numeric','min:0','max:100'],
            'values.lymphocytes_pct' => ['nullable','numeric','min:0','max:100'],
            'values.monocytes_pct' => ['nullable','numeric','min:0','max:100'],
            'values.eosinophils_pct' => ['nullable','numeric','min:0','max:100'],
            'values.basophils_pct' => ['nullable','numeric','min:0','max:100'],
            'values.rbc' => ['nullable','numeric','min:0','max:20'],
            'values.hb' => ['nullable','numeric','min:0','max:30'],
            'values.hct' => ['nullable','numeric','min:0','max:100'],
            'values.rdw_cv' => ['nullable','numeric','min:0','max:100'],
            'values.plt' => ['nullable','numeric','min:0','max:5000'],
            'values.mpv' => ['nullable','numeric','min:0','max:20'],
        ];
    }
}
