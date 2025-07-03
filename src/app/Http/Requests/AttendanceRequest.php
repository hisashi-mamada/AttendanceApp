<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'clock_in_time'  => ['required', 'date_format:H:i', 'before:clock_out_time'],
            'clock_out_time' => ['required', 'date_format:H:i', 'after:clock_in_time'],
            'remarks'        => ['required'],

            // 休憩時間（可変）
            'breaks.*.start' => ['nullable', 'date_format:H:i'],
            'breaks.*.end'   => ['nullable', 'date_format:H:i'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $clockIn = $this->input('clock_in_time');
            $clockOut = $this->input('clock_out_time');

            foreach ($this->input('breaks', []) as $index => $break) {
                $start = $break['start'] ?? null;
                $end = $break['end'] ?? null;

                // 勤務時間内チェック
                if ($start && ($start < $clockIn || $start > $clockOut)) {
                    $validator->errors()->add("breaks.$index.start", '休憩時間が勤務時間外です');
                }

                if ($end && ($end < $clockIn || $end > $clockOut)) {
                    $validator->errors()->add("breaks.$index.end", '休憩時間が勤務時間外です');
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'clock_in_time.before' => '出勤時間もしくは退勤時間が不適切な値です',
            'clock_out_time.after' => '出勤時間もしくは退勤時間が不適切な値です',
            'remarks.required' => '備考を記入してください',
        ];
    }
}
