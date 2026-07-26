<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class TabdepotRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
             'typdm'=> 'required',
             'nom'=> 'required|max:100',
            'nni'=> 'required|digits:10',
          'tel'=>'required|digits:8',
          '	adresse'=>'required',
           'daterecp'=>'required',
        ];
    }

public function message()
    { 
        return [
           'typdm'=> 'يرجى اختيار نوع الطلب.',

             'nom.required'=> 'اسم الشخص مطلوب.',
             'nom.max' => 'اسم الشخص  يجب ألا يزيد عن 100 حرف.',
           'nni.required' => 'nni مطلوبة.',
            'nni.digits' => 'nni يجب أن يتكون  من عشرة أرقام.',
            'tel.required' => 'tel مطلوبة.',
            'tel.digits' => 'tel يجب أن يتكون  من 8 أرقام.',
          '	adresse'=>'العنوان مطلوب',
           'daterecp'=>'التاريخ مطلوب',

    ];
    }

}
