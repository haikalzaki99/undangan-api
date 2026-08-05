<?php

namespace App\Request;

use Core\Valid\Form;

class InsertCommentRequest extends Form
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => ['nullable', 'str', 'trim', 'uuid', 'max:37'],
            'name' => ['required', 'str', 'trim', 'min:1', 'max:40'],
            'presence' => ['bool'],
            'comment' => ['nullable', 'str', 'min:1', 'max:1000'],
            'gif_id' => ['nullable', 'int', 'min:1', 'max:100']
        ];
    }
}
