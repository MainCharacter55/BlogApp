<?php
// app/Http/Requests/UpdateApiCommentRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * API のコメント更新リクエストを検証する FormRequest。
 */
class UpdateApiCommentRequest extends FormRequest
{
    /**
     * リクエストの認可可否を判定する。
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * バリデーションルールを定義する。
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'content' => ['required', 'string', 'min:10', 'max:100'],
        ];
    }

    /**
     * エラーメッセージを定義する。
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'content.required' => 'コメント内容を入力してください。',
            'content.min' => 'コメントは10文字以上で入力してください。',
            'content.max' => 'コメントは100文字以内で入力してください。',
        ];
    }
}
