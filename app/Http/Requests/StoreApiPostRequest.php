<?php
// app/Http/Requests/StoreApiPostRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * 投稿作成 API リクエストを検証する FormRequest。
 */
class StoreApiPostRequest extends FormRequest
{
    /**
     * リクエストの認可可否を判定する。
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->isAdmin();
    }

    /**
     * バリデーションルールを定義する。
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string', 'min:20'],
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
            'title.required' => 'タイトルを入力してください。',
            'title.max' => 'タイトルは255文字以内で入力してください。',
            'content.required' => '本文を入力してください。',
            'content.min' => '本文は20文字以上で入力してください。',
        ];
    }
}
