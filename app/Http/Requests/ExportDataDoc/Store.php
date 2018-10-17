<?php

namespace App\Http\Requests\ExportDataDoc;

use Illuminate\Foundation\Http\FormRequest;

class Store extends FormRequest
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
            'host' => 'required',
            'port' => 'required',
            'database' => 'required',
            'username' => 'required',
        ];
    }
}
