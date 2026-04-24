<?php
// app/Http/Requests/StoreApiCommentRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreApiCommentRequest extends FormRequest
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
            'parent_id' => ['nullable', 'integer', 'exists:comments,id'],
        ];
    }
}
