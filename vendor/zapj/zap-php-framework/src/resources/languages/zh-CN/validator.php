<?php

/**
 * validator 中文语言包
 *
 * {value} 输入值
 * {param} 条件参数
 * {field} 标签值
 */
return [
    'rule_url'             => '无效的URL',
    'rule_max'             => '{field}必须小于或等于 {param}',
    'rule_min'             => '{field}必须大于或等于 {param}',
    'rule_required'        => '{field}不能为空',
    'rule_in'              => '{field}必须在 {param} 范围内',
    'rule_not_in'          => '{field}不能在 {param} 范围内',
    'rule_is_array'        => '{field}必须是一个数组',
    'rule_ascii'           => '{field}只能包含ASCII字符',
    'rule_between'         => '{field}必须在 {0} ~ {1} 之间',
    'rule_callback'        => '{field}验证未通过',
    'rule_regex'           => '{field}格式无效',
    'rule_domain'          => '{field}不是一个有效的域名',
    'rule_double'          => '{field}不是一个有效的浮点数',
    'rule_email'           => '{field}不是一个有效的邮箱地址',
    'rule_integer'         => '{field}不是一个有效的整数',
    'rule_ip'              => '{field}不是一个有效的IP地址',
    'rule_ipv4'            => '{field}不是一个有效的IPv4地址',
    'rule_ipv6'            => '{field}不是一个有效的IPv6地址',
    'rule_length'          => '{field}长度必须在 {0} ~ {1} 之间',
    'rule_length_max'      => '{field}长度不能超过 {param}',
    'rule_length_min'      => '{field}长度不能小于 {param}',
    'rule_range_length'    => '{field}长度必须在 {0} ~ {1} 之间',
    'rule_numeric'         => '{field}必须为数字',
    'rule_required_with'   => '{field}不能为空',
    'rule_alpha'           => '{field}只能包含英文字母(a-z)',
    'rule_alpha_num'       => '{field}只能包含英文字母(a-z)和数字(0-9)',

    // 新增规则
    'rule_confirmed'       => '{field}两次输入不一致',
    'rule_date'            => '{field}不是一个有效的日期',
    'rule_date_format'     => '{field}日期格式不正确',
    'rule_boolean'         => '{field}必须为布尔值',
    'rule_json'            => '{field}不是有效的JSON格式',
    'rule_distinct'        => '{field}包含重复的值',
    'rule_same'            => '{field}与 {param} 不一致',
    'rule_different'       => '{field}不能与 {param} 相同',
];
