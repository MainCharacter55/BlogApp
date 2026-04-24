<?php
// app/Http/Requests/Web/SetReactionRequest.php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SetReactionRequest extends FormRequest
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
            'reaction' => [
                'required',
                'string',
                Rule::in(array_keys(config('reactions.options', []))),
            ],
            'comment_sort' => ['nullable', 'in:new,popular'],
        ];
    }
}
