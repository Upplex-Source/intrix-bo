<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => ':attribute必须被接受。',
    'active_url' => ':attribute不是一个有效的URL。',
    'after' => ':attribute必须是:date之后的一个日期。',
    'after_or_equal' => ':attribute必须是等于或晚于:date的日期。',
    'alpha' => ':attribute只能包含字母。',
    'alpha_dash' => ':attribute只能包含字母、数字、破折号和下划线。',
    'alpha_num' => ':attribute只能包含字母和数字。',
    'array' => ':attribute必须是一个数组。',
    'before' => ':attribute必须是:date之前的一个日期。',
    'before_or_equal' => ':attribute必须是等于或早于:date的日期。',
    'between' => [
        'numeric' => ':attribute必须介于:min和:max之间。',
        'file' => ':attribute必须介于:min和:max千字节之间。',
        'string' => ':attribute必须介于:min和:max个字符之间。',
        'array' => ':attribute必须包含:min和:max个项目。',
    ],
    'boolean' => ':attribute字段必须为true或false。',
    'confirmed' => '确认的:attribute不相同。',
    'current_password' => '密码不正确。',
    'date' => ':attribute不是一个有效的日期。',
    'date_equals' => ':attribute必须等于:date。',
    'date_format' => ':attribute不匹配格式:format。',
    'declined' => ':attribute必须被拒绝。',
    'declined_if' => '当:other为:value时，:attribute必须被拒绝。',
    'different' => ':attribute和:other必须不同。',
    'digits' => ':attribute必须是:digits位的数字。',
    'digits_between' => ':attribute的数字必须介于:min和:max之间。',
    'dimensions' => ':attribute具有无效的图像尺寸。',
    'distinct' => ':attribute字段具有重复值。',
    'email' => ':attribute不是一个合法的邮箱地址。',
    'ends_with' => ':attribute必须以以下之一结束:values。',
    'enum' => '选择的:attribute是无效的。',
    'exists' => '选择的:attribute是无效的。',
    'file' => ':attribute必须是一个文件。',
    'filled' => ':attribute字段必须有一个值。',
    'gt' => [
        'numeric' => ':attribute必须大于:value。',
        'file' => ':attribute必须大于:value千字节。',
        'string' => ':attribute必须大于:value个字符。',
        'array' => ':attribute必须包含多于:value个项目。',
    ],
    'gte' => [
        'numeric' => ':attribute必须大于或等于:value。',
        'file' => ':attribute必须大于或等于:value千字节。',
        'string' => ':attribute必须大于或等于:value个字符。',
        'array' => ':attribute必须包含:value个项目或更多。',
    ],
    'image' => ':attribute必须是一张图片。',
    'in' => '选择的:attribute是无效的。',
    'in_array' => ':attribute字段不存在于:other中。',
    'integer' => ':attribute必须是整数。',
    'ip' => ':attribute必须是一个有效的IP地址。',
    'ipv4' => ':attribute必须是一个有效的IPv4地址。',
    'ipv6' => ':attribute必须是一个有效的IPv6地址。',
    'json' => ':attribute必须是一个有效的JSON字符串。',
    'lt' => [
        'numeric' => ':attribute必须小于:value。',
        'file' => ':attribute必须小于:value千字节。',
        'string' => ':attribute必须小于:value个字符。',
        'array' => ':attribute必须包含少于:value个项目。',
    ],
    'lte' => [
        'numeric' => ':attribute必须小于或等于:value。',
        'file' => ':attribute必须小于或等于:value千字节。',
        'string' => ':attribute必须小于或等于:value个字符。',
        'array' => ':attribute必须不超过:value个项目。',
    ],
    'mac_address' => ':attribute必须是一个有效的MAC地址。',
    'max' => [
        'numeric' => ':attribute不能大于:max。',
        'file' => ':attribute不能大于:max千字节。',
        'string' => ':attribute不能大于:max个字符。',
        'array' => ':attribute不能包含超过:max个项目。',
    ],
    'mimes' => ':attribute必须是类型之一的文件: :values。',
    'mimetypes' => ':attribute必须是类型之一的文件: :values。',
    'min' => [
        'numeric' => ':attribute至少必须是:min。',
        'file' => ':attribute至少必须是:min千字节。',
        'string' => ':attribute至少必须有:min个字符。',
        'array' => ':attribute至少必须包含:min个项目。',
    ],
    'multiple_of' => ':attribute必须是:value的倍数。',
    'not_in' => '选择的:attribute是无效的。',
    'not_regex' => ':attribute格式无效。',
    'numeric' => ':attribute必须是一个数字。',
    'password' => '密码不正确。',
    'present' => ':attribute字段必须存在。',
    'prohibited' => ':attribute字段是禁止的。',
    'prohibited_if' => '当:other为:value时，:attribute字段是禁止的。',
    'prohibited_unless' => '除非:other是:values中的一个，否则:attribute字段是禁止的。',
    'prohibits' => ':attribute字段禁止:other存在。',
    'regex' => ':attribute格式无效。',
    'required' => ':attribute不能为空。',
    'required_array_keys' => ':attribute字段必须包含以下条目: :values。',
    'required_if' => '当:other为:value时，:attribute字段是必需的。',
    'required_unless' => '除非:other是:values中的一个，否则:attribute字段是必需的。',
    'required_with' => '当:values存在时，:attribute字段是必需的。',
    'required_with_all' => '当所有:values都存在时，:attribute字段是必需的。',
    'required_without' => '当:values不存在时，:attribute字段是必需的。',
    'required_without_all' => '当所有:values都不存在时，:attribute字段是必需的。',
    'same' => ':attribute和:other必须匹配。',
    'size' => [
        'numeric' => ':attribute必须是:size。',
        'file' => ':attribute必须是:size千字节。',
        'string' => ':attribute必须是:size个字符。',
        'array' => ':attribute必须包含:size个项目。',
    ],
    'starts_with' => ':attribute必须以以下之一开始:values。',
    'string' => ':attribute必须是一个字符串。',
    'timezone' => ':attribute必须是一个有效的时区。',
    'unique' => ':attribute已经存在。',
    'uploaded' => ':attribute上传失败。',
    'url' => ':attribute必须是一个有效的URL。',
    'uuid' => ':attribute必须是一个有效的UUID。',
    'non_ascii_character_not_allowed' => ':attribute不能包含非ASCII字符',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [
        'one_time_password' => '一次性密码',
    ],

];
