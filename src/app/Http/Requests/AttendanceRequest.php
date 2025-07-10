<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class AttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'requested_clock_in_time'  => ['required', 'date_format:H:i', 'before:requested_clock_out_time'],
            'requested_clock_out_time' => ['required', 'date_format:H:i', 'after:requested_clock_in_time'],
            'reason'                   => ['required'],
            'breaks.*.start'           => ['nullable', 'date_format:H:i'],
            'breaks.*.end'             => ['nullable', 'date_format:H:i'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $date = $this->route('id')
                ? \App\Models\Attendance::find($this->route('id'))?->date
                : now()->toDateString();

            try {
                $clockIn  = Carbon::createFromFormat('Y-m-d H:i', $date . ' ' . $this->input('requested_clock_in_time'));
                $clockOut = Carbon::createFromFormat('Y-m-d H:i', $date . ' ' . $this->input('requested_clock_out_time'));

                foreach ($this->input('breaks', []) as $index => $break) {
                    $startStr = $break['start'] ?? null;
                    $endStr   = $break['end'] ?? null;

                    $start = $startStr ? Carbon::createFromFormat('Y-m-d H:i', $date . ' ' . $startStr) : null;
                    $end   = $endStr   ? Carbon::createFromFormat('Y-m-d H:i', $date . ' ' . $endStr)   : null;

                    if ($start && ($start->lt($clockIn) || $start->gt($clockOut))) {
                        $validator->errors()->add("breaks.$index.start", '休憩時間が勤務時間外です');
                    }

                    if ($end && ($end->lt($clockIn) || $end->gt($clockOut))) {
                        $validator->errors()->add("breaks.$index.end", '休憩時間が勤務時間外です');
                    }
                }
            } catch (\Exception $e) {
                $validator->errors()->add('requested_clock_in_time', '時間の形式が正しくありません');
            }
        });
    }

    public function messages(): array
    {
        return [
            'requested_clock_in_time.before'  => '出勤時間もしくは退勤時間が不適切な値です',
            'requested_clock_out_time.after'  => '出勤時間もしくは退勤時間が不適切な値です',
            'reason.required'                 => '備考を記入してください',
        ];
    }
}
