<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UnallowableBookRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'File' => ['required','file','mimes:xlsx,xlx,xls']
        ];
    }

    public function messages(): array
    {
        return [
            '*.required' => trans('persianErrors.required'),
            '*.mimes' => trans('persianErrors.mimes'),
            '*.string' => trans('persianErrors.string'),
            '*.integer' => trans('persianErrors.integer'),
            '*.file' => trans('persianErrors.file'),
            '*.min' => trans('persianErrors.min'),
            '*.max' => trans('persianErrors.max'),
            '*.gt' => trans('persianErrors.gte'),

        ];
    }
}
