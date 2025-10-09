<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DetailRequest extends FormRequest
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
            'clock_in' => 'nullable|date_format:H:i',
            'clock_out' => 'nullable|date_format:H:i|after:clock_in',

            'breaks.0.start' => 'nullable|date_format:H:i|after:clock_in|before:clock_out',
            'breaks.0.end' => 'nullable|date_format:H:i|before:clock_out',

            'breaks.2.start' => 'nullable|date_format:H:i|after:clock_in|before:clock_out',
            'breaks.2.end' => 'nullable|date_format:H:i|before:clock_out',

            'remarks' => 'required|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'clock_out.after' => '出勤時間もしくは退勤時間が不適切な値です',

            'breaks.0.start.after' => '休憩時間が不適切な値です',
            'breaks.0.start.before' => '休憩時間が不適切な値です',
            'breaks.0.end.before' => '休憩時間もしくは退勤時間が不適切な値です',

            'breaks.2.start.after' => '休憩時間が不適切な値です',
            'breaks.2.start.before' => '休憩時間が不適切な値です',
            'breaks.2.end.before' => '休憩時間もしくは退勤時間が不適切な値です',

            'remarks.required' => '備考を記入してください',
            'remarks.max' => '文字数は255文字以内で入力してください',
        ];
    }
}