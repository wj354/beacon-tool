<?php

namespace tool\form;

use beacon\Form;

class InstallForm extends Form
{
    public $title = '安装系统';

    protected function load()
    {
        return [
            'db_host' => [
                'label' => '数据库HOST：',
                'data-val-rule' => ['r' => true],
                'data-val-message' => ['r' => '请输入数据库HOST'],
            ],
            'db_port' => [
                'label' => '数据库端口',
                'type' => 'integer',
                'data-val-rule' => ['r' => true],
                'data-val-message' => ['r' => '请输入数据库端口'],
            ],
            'db_name' => [
                'label' => '数据库名称',
                'data-val-rule' => ['r' => true],
                'data-val-message' => ['r' => '请输入数据库名称'],
            ],
            'db_user' => [
                'label' => '数据库账号',
                'data-val-rule' => ['r' => true],
                'data-val-message' => ['r' => '请输入数据库账号'],
            ],
            'db_pwd' => [
                'label' => '数据库密码',
                'data-val-rule' => ['r' => true],
                'data-val-msg' => ['r' => '请输入数据库密码'],
            ],
            'db_prefix' => [
                'label' => '数据库表前缀',
                'data-val-rule' => ['r' => true, 'regex' => '^\w+$'],
                'data-val-message' => ['r' => '请输入数据库表前缀', 'regex' => '格式不正确'],
            ],
        ];
    }
}