<?php

return [
    'app_key' => [
        'label' => 'APP Key',
        'required' => true,
        'valueType' => 'string',
        'min' => 1,
        'max' => 100
    ],
    'app_secret' => [
        'label' => 'APP Secret',
        'required' => true,
        'valueType' => 'string',
        'min' => 1,
        'max' => 100
    ],
];