<?php


namespace tool\model;


use beacon\core\DB;
use beacon\core\DBException;
use beacon\core\Form;
use beacon\core\Request;
use beacon\widget\Check;
use beacon\widget\Integer;
use beacon\widget\Text;

#[Form(title: '项目管理', table: '@pf_tool_app', template: 'form/default.tpl')]
class AppModel
{
    #[Text(
        label: '项目名称',
        validRule: ['r' => '请输入项目名称'],
        prompt: '如：后台管理',
        star: true,
        attrs: ['placeholder' => '如：后台管理', 'yee-module' => 'remote', 'data-url' => '~/index/check_name']
    )]
    public string $name = '';


    #[Text(
        label: '命名空间',
        validRule: ['r' => '请输入命名空间', 'regex' => ['^[a-z0-9]+(\\\\[a-z0-9]+)*$', '命名空间格式不正确']],
        prompt: '请输入项目命名空间,用于自动加载和生成文件',
        star: true,
        attrs: ['placeholder' => '如：app\admin']
    )]
    public string $namespace = '';


    #[Text(
        label: '路由模块名',
        validRule: ['r' => '没有填写路由模块名', 'regex' => ['^[a-zA-Z][A-Za-z0-9_]*$', '路由模块名只能使用字母开头的数字及字母组合']],
        prompt: '如果模块名称不正确，则无法测试列表，将会查找对应的路由规则',
        star: true,
        attrs: ['placeholder' => '如：admin', 'style' => 'width:120px;']
    )]
    public string $module = '';


    #[Check(
        label: '是否默认项目',
        prompt: '勾选设置成为默认项目',
        star: true,
    )]
    public int $isDefault = 0;


    #[Text(
        label: '项目目录',
        prompt: '如果需要跨应用生成，可填写项目目录，如果为空即当前项目',
        attrs: ['placeholder' => '如：/home/www/xxx']
    )]
    public string $dirName = '';

    #[Text(
        label: '数据库Host',
        prompt: '如果需要跨应用生成可填写，为空即当前项目',
    )]
    public string $db_host = '';

    #[Integer(
        label: '数据库端口',
        prompt: '如果需要跨应用生成可填写，为空即当前项目',
    )]
    public ?int $db_port = null;


    #[Text(
        label: '数据库Name',
        prompt: '如果需要跨应用生成可填写，为空即当前项目',
    )]
    public string $db_name = '';

    #[Text(
        label: '数据库账号',
        prompt: '如果需要跨应用生成可填写，为空即当前项目',
    )]
    public string $db_user = '';


    #[Text(
        label: '数据库密码',
        prompt: '如果需要跨应用生成可填写，为空即当前项目',
    )]
    public string $db_pwd = '';

    #[Text(
        label: '数据库前缀',
        prompt: '如果需要跨应用生成可填写，为空即当前项目',
    )]
    public string $db_prefix = '';

    #[Text(
        label: '数据库字符集',
        prompt: '如果需要跨应用生成可填写，为空即当前项目',
    )]
    public string $db_charset = 'utf8';

    /**
     * @param string $value
     * @return bool
     * @throws DBException
     */
    public static function checkName(string $value): bool
    {
        $id = Request::param('id:i', 0);
        $row = DB::getRow('select id from @pf_tool_app where `name`=? and id<>?', [$value, $id]);
        if ($row) {
            return false;
        }
        return true;
    }

}