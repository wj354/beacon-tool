<?php
/**
 * Created by PhpStorm.
 * User: wj008
 * Date: 18-11-26
 * Time: 上午4:23
 */

namespace tool\install;


use beacon\Config;
use beacon\Controller;
use beacon\Mysql;
use beacon\DBException;
use beacon\Utils;
use tool\form\InstallForm;
use tool\Tool;

class Install extends Controller
{
    public function indexAction()
    {
        $this->display('install/Install');
    }

    private function checkDir($dir, &$info = array())
    {
        $dir = Utils::path(ROOT_DIR, $dir);
        $rt = $this->checkIsWritable($dir);
        if ($rt) {
            $info[] = "<span class=yes>YES</span>--- {$dir} 目录存在 可写。";
        } else {
            $info[] = "<span class=no>NO</span>--- {$dir} 目录权限不足，请修改目录读写权限。";
        }
        return $rt;
    }

    private function checkFile($file, &$info = array())
    {
        $file = Utils::path(ROOT_DIR, $file);
        if (file_exists($file)) {
            if (is_writable($file)) {
                $info[] = "<span class=yes>YES</span>--- {$file} 文件存在 可写。";
                return true;
            } else {
                $info[] = "<span class=no>NO</span>--- {$file} 文件权限不足，请修改文件读写权限。";
                return false;
            }
        }
        return true;
    }

    private function checkIsWritable($dirPath)
    {
        $dirPath = Utils::path($dirPath);
        Utils::makeDir($dirPath);
        if (!is_dir($dirPath)) {
            return false;
        } else {
            $handle = @fopen($dirPath . 'beacon__test.txt', 'w');
            if (!$handle) {
                return false;
            } else {
                @fclose($handle);
                @unlink($dirPath . 'beacon__test.txt');
            }
        }
        return true;
    }


    public function checkAction()
    {
        $info = [];
        $ok = true;
        //判断配置文件夹 是否存在可写
        $ok = $this->checkDir('/config', $info) && $ok;
        $ok = $this->checkDir('/app', $info) && $ok;
        $ok = $this->checkDir('/runtime', $info) && $ok;
        $ok = $this->checkFile('/config/db.config.php', $info) && $ok;
        $html = join('<br>', $info);
        $this->assign('info', $html);
        $this->assign('ok', $ok);
        $this->display('install/InstallCheck');
    }

    public function databaseAction()
    {
        $form = new InstallForm('add');
        if ($this->isGet()) {
            $data = Config::get('db.*', [
                'db_host' => '127.0.0.1',
                'db_port' => 3306,
                'db_name' => '',
                'db_user' => 'root',
                'db_pwd' => '',
                'db_prefix' => 'sl_'
            ]);
            $form->setValues($data);
            $this->assign('form', $form);
            $this->display('install/InstallDb');
            return;
        }

        if ($this->isPost()) {
            $values = $form->autoComplete();
            if (!$form->validation($error)) {
                $this->error($error);
            }
            try {
                //检查创建数据库

                $db = new Mysql($values['db_host'], $values['db_port'], '', $values['db_user'], $values['db_pwd'], $values['db_prefix']);
                $db->exec('CREATE DATABASE if not exists `' . $values['db_name'] . '` DEFAULT CHARSET utf8 COLLATE utf8_general_ci');
                $code = '<?php return ' . var_export($values, TRUE) . ';';
                $file = Utils::path(ROOT_DIR, '/config/db.config.php');
                file_put_contents($file, $code);
                Tool::install();
            } catch (DBException $exception) {
                $this->error('保存失败：' . $exception->getMessage());
            } catch (\Exception $exception) {
                $this->error('保存失败：' . $exception->getMessage());
            }
            $this->success('保存成功', ['back' => '/admin']);
        }
    }
}