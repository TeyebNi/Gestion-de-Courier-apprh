<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RemarqueRequest extends FormRequest
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
           
         'PersonID'=>'required',
          'nni'=>'required',
          'nom'=>'required',
          'fonction'=>'required',
          'typecontrat'=>'required',
          'tel'=>'required',
           'datenaiss'=>'required',
           'lieuness'=>'required',
           'debutcontrat'=>'required',
           'fincontrat'=>'required',
           'usermodif'=>'required',
            'obsv'=>'required',
             'statut'=>'required',
       
        ];
    }
    public function message()
    { 
        return [
            'PersonID.required'=>'the name required',
          'nni.required'=>'the field required',
          'nom.required'=>'the field required',
          'fonction.required'=>'the field required',
          'typecontrat.required'=>'the field required',
          'tel.required'=>'the field required',
           'datenaiss.required'=>'the field required',
           'lieuness.required'=>'the field required',
           'debutcontrat.required'=>'the field required',
           'fincontrat.required'=>'the field required',
           'usermodif.required'=>'the field required',
            'obsv.required'=>'the field required',
             'statut.required'=>'the field required',
    ];
    }
}
