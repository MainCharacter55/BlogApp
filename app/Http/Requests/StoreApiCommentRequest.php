<?php
// app/Http/Requests/StoreApiCommentRequest.php

namespace App\Http\Requests;

use App\Models\Post;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Database\Query\Builder;
use Illuminate\Validation\Rule;

/**
 * API のコメント作成リクエストを検証する FormRequest。
 */
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
        $post = $this->route('post');

        $parentRule = ['nullable', 'integer'];

        if ($post instanceof Post) {
            $parentRule[] = Rule::exists('comments', 'id')->where(function (Builder $query) use ($post): void {
                $query->where('post_id', $post->id);
            });
        } else {
            $parentRule[] = 'exists:comments,id';
        }

        return [
            'content' => ['required', 'string', 'min:10', 'max:100'],
            'parent_id' => $parentRule,
        ];
    }
}
