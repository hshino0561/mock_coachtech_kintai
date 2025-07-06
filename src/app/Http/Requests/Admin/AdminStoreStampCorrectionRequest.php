<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AdminStoreStampCorrectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time'   => ['nullable', 'date_format:H:i'],
            'breaks.*.start' => ['nullable', 'date_format:H:i'],
            'breaks.*.end'   => ['nullable', 'date_format:H:i'],
            'memo' => ['required', 'string'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $start = $this->input('start_time');
            $end   = $this->input('end_time');

            // 要件① 出勤 > 退勤 または 退勤 < 出勤 の場合
            if ($start && $end && $start > $end) {
                $validator->errors()->add('start_time', '出勤時間もしくは退勤時間が不適切な値です。');
                $validator->errors()->add('end_time', '出勤時間もしくは退勤時間が不適切な値です。');
            }

            // デバッグ用ログ出力
            \Log::debug('勤務時間(管理者): ', ['start_time' => $start, 'end_time' => $end]);
            \Log::debug('全休憩データ(管理者): ', $this->input('breaks', []));
            \Log::debug('バリデーションエラー一覧(管理者): ', $validator->errors()->toArray());

            // 要件② 休憩時間が勤務時間外
            foreach ($this->input('breaks', []) as $index => $break) {
                $breakStart = $break['start'] ?? null;
                $breakEnd   = $break['end'] ?? null;
            
                if ($start && $end && $breakStart && $breakEnd) {
                    if ($breakStart < $start || $breakEnd > $end || $breakStart > $breakEnd) {
                        $label = $index + 1;
                        $validator->errors()->add("breaks.$index.start", "休憩{$label}の時間が勤務時間外です。");
                        $validator->errors()->add("breaks.$index.end", "休憩{$label}の時間が勤務時間外です。");
                    }
                }
            }
        });
    }

    public function attributes(): array
    {
        return [
            'start_time' => '出勤時間',
            'end_time' => '退勤時間',
            'breaks.*.start' => '休憩開始時間',
            'breaks.*.end' => '休憩終了時間',
            'memo' => '備考',
        ];
    }

    public function messages(): array
    {
        return [
            'memo.required' => '備考を記入してください。',
        ];
    }
}
