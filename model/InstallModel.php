<?php


namespace tool\model;

use beacon\core\Form;
use beacon\widget\Text;
use beacon\widget\Integer;
use beacon\widget\Select;

#[Form(title: '安装系统')]
class InstallModel
{
    #[Text(
        label: '数据库HOST',
        validRule: ['r' => '请输入数据库HOST'],
    )]
    public string $db_host = '127.0.0.1';
    #[Integer(
        label: '数据库端口',
        validRule: ['r' => '请输入数据库端口'],
    )]
    public int $db_port = 3306;

    #[Text(
        label: '数据库名称',
        validRule: ['r' => '请输入数据库名称'],
    )]
    public string $db_name = '';
    #[Text(
        label: '数据库账号',
        validRule: ['r' => '请输入数据库账号'],
    )]
    public string $db_user = 'root';
    #[Text(
        label: '数据库密码',
        validRule: ['r' => '请输入数据库密码'],
    )]
    public string $db_pwd = '';
    #[Text(
        label: '数据库表前缀',
        validRule: ['r' => '请输入数据库表前缀'],
    )]
    public string $db_prefix = 'sl_';

    #[Select(
        label: '字符集编码',
        options: [
            ['value' => 'utf8mb4', 'text' => 'utf8mb4'],
            ['value' => 'utf8', 'text' => 'utf8']
        ]
    )]
    public string $db_charset = 'utf8mb4';

}