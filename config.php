<?php

use \beacon\Config;

return [
    'sdopx.extension' => 'tpl',
    'sdopx.template_dir' => (function () {
        return Config::get('tool.template_dir', [__DIR__ . '/view']);
    })(),
    //谷歌翻译
    'translate.type' => (function () {
        return Config::get('tool.translate_type', 'google');
    })(),
    //百度翻译账号秘钥
    //'translate.type'=>'baidu',
    //'translate.appid' => '20180212000122391',
    //'translate.appkey' => 'ppa8TQ6cyQkxoknvnO_8',
    //支持的方法
    'tool.support_action' => (function () {
        $data = [
            ['value' => 'add', 'text' => '添加 add'],
            ['value' => 'sort', 'text' => '排序 sort'],
            ['value' => 'toggleAllow', 'text' => '审核/禁用 toggleAllow'],
            ['value' => 'edit', 'text' => '编辑 edit'],
            ['value' => 'delete', 'text' => '删除 delete'],
            ['value' => 'deleteChoice', 'text' => '删除所选 deleteChoice'],
            ['value' => 'allowChoice', 'text' => '审核所选 allowChoice'],
            ['value' => 'revokeChoice', 'text' => '禁用所选 revokeChoice'],
        ];
        $append = Config::get('tool.append_support_action', null);
        if (is_array($append)) {
            $data = array_merge($data, $append);
        }
        return $data;
    })(),

    //支持的类型
    'tool.support_type' => (function () {
        $data = [
            ['value' => 'text', 'text' => '文本框 text', 'data-bind' => ['varchar(100)', 'text']],
            ['value' => 'hidden', 'text' => '隐藏域 hidden', 'data-bind' => ['int(11)', 'tinyint(4)', 'varchar(100)', 'float(18,2)', 'decimal(18,2)', 'double(18,2)', 'text', 'longtext', 'json'], 'data-default' => 'int'],
            ['value' => 'check', 'text' => '是否 check', 'data-bind' => ['tinyint(1)']],
            ['value' => 'integer', 'text' => '整数 integer', 'data-bind' => ['int(11)']],
            ['value' => 'number', 'text' => '小数 number', 'data-bind' => ['decimal(18,2)', 'float(18,2)', 'double(18,2)']],
            ['value' => 'password', 'text' => '密码框 password', 'data-bind' => ['varchar(100)']],
            // ['value' => 'color', 'text' => '颜色选择 color', 'data-bind' => ['varchar(100)']],
            ['value' => 'date', 'text' => '日期格式 date', 'data-bind' => ['date', 'datetime', 'int(11)']],
            ['value' => 'datetime', 'text' => '时间格式 datetime', 'data-bind' => ['datetime', 'int(11)']],
            ['value' => 'select', 'text' => '下拉框 select', 'data-bind' => ['int(11)', 'varchar(100)'], 'data-default' => 'int'],
            ['value' => 'delay-select', 'text' => '异步下拉 delay-select', 'data-bind' => ['int(11)', 'varchar(100)'], 'data-default' => 'int'],
            ['value' => 'radio-group', 'text' => '单选组 radio-group', 'data-bind' => ['int(11)', 'varchar(100)'], 'data-default' => 'int'],
            ['value' => 'check-group', 'text' => '多选组 check-group', 'data-bind' => ['varchar(200)', 'int(11)', 'text', 'longtext', 'json'], 'data-default' => 'json'],
            ['value' => 'linkage', 'text' => '联动下拉 linkage', 'data-bind' => ['varchar(200)', 'text', 'json'], 'data-default' => 'json'],
            ['value' => 'textarea', 'text' => '备注型 textarea', 'data-bind' => ['text', 'varchar(200)', 'longtext'], 'data-default' => 'text'],
            ['value' => 'up-file', 'text' => '文件上传 up-file', 'data-bind' => ['varchar(200)', 'text', 'json']],
            ['value' => 'up-image', 'text' => '图片上传 up-image', 'data-bind' => ['varchar(300)', 'text', 'json']],
            ['value' => 'xh-editor', 'text' => 'Xh编辑器 xh-editor', 'data-bind' => ['text', 'longtext']],
            ['value' => 'tinymce', 'text' => 'tiny编辑器 tinymce', 'data-bind' => ['text', 'longtext']],
            ['value' => 'select-dialog', 'text' => '选择对话框 select-dialog', 'data-bind' => ['int(11)', 'varchar(100)'], 'data-default' => 'int'],
            ['value' => 'multiple-dialog', 'text' => '多选对话框 multiple-dialog', 'data-bind' => ['text', 'varchar(200)', 'json']],
            ['value' => 'line', 'text' => '分割行 line', 'data-bind' => ['none']],
            ['value' => 'label', 'text' => '标签 label', 'data-bind' => ['none']],
            ['value' => 'button', 'text' => '按钮 button', 'data-bind' => ['none']],
            ['value' => 'container', 'text' => '插件容器 container', 'data-bind' => ['text', 'json']],
        ];
        $append = Config::get('tool.append_support_type', null);
        if (is_array($append)) {
            $data = array_merge($data, $append);
        }
        return $data;
    })(),
    //搜索类型
    'tool.search_type' => (function () {
        $data = [
            ['value' => 'text', 'text' => '文本框 text', 'data-type' => ['varchar(100)', 'text']],
            ['value' => 'hidden', 'text' => '隐藏域 hidden', 'data-type' => ['int(11)', 'tinyint(4)', 'varchar(100)', 'float(18,2)', 'decimal(18,2)', 'double(18,2)', 'text', 'longtext', 'json']],
            ['value' => 'check', 'text' => '是否 check', 'data-type' => ['tinyint(1)']],
            ['value' => 'integer', 'text' => '整数 integer', 'data-type' => ['int(11)']],
            ['value' => 'number', 'text' => '小数 number', 'data-type' => ['decimal(18,2)', 'float(18,2)', 'double(18,2)']],
            ['value' => 'password', 'text' => '密码框 password', 'data-type' => ['varchar(100)']],
            ['value' => 'date', 'text' => '日期格式 date', 'data-type' => ['date', 'datetime', 'int(11)']],
            ['value' => 'datetime', 'text' => '时间格式 datetime', 'data-type' => ['datetime', 'int(11)']],
            ['value' => 'select', 'text' => '下拉框 select', 'data-type' => ['int(11)', 'varchar(100)']],
            ['value' => 'delay-select', 'text' => '异步下拉 delay-select', 'data-bind' => ['int(11)', 'varchar(100)'], 'data-default' => 'int'],
            ['value' => 'radio-group', 'text' => '单选组 radio-group', 'data-type' => ['int(11)', 'varchar(100)']],
            ['value' => 'check-group', 'text' => '多选组 check-group', 'data-type' => ['varchar(200)', 'int(11)', 'text', 'longtext', 'json']],
            ['value' => 'select-dialog', 'text' => '选择对话框 select-dialog', 'data-type' => ['int(11)', 'varchar(100)']],
            ['value' => 'linkage', 'text' => '联动下拉 linkage', 'data-type' => ['varchar(200)', 'text', 'json']],
            ['value' => 'button', 'text' => '按钮 button'],
        ];
        $append = Config::get('tool.append_search_type', null);
        if (is_array($append)) {
            $data = array_merge($data, $append);
        }
        return $data;
    })(),
];
