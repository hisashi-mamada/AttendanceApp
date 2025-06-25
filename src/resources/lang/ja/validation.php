<?php

return [
    'required' => ':attribute は必須項目です。',
    'email' => ':attribute には有効なメールアドレスを入力してください。',
    'min' => [
        'string' => ':attribute は :min 文字以上で入力してください。',
    ],
    'confirmed' => ':attribute と確認用パスワードが一致しません。',

    'attributes' => [
        'name' => 'ユーザー名',
        'email' => 'メールアドレス',
        'password' => 'パスワード',
        'password_confirmation' => '確認用パスワード',
    ],
];
