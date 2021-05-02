<?php


namespace tool\controller;


use beacon\core\Config;
use beacon\core\Controller;
use beacon\core\DB;
use beacon\core\Method;
use beacon\core\Util;
use beacon\core\Form;
use beacon\core\Mysql;
use beacon\core\DBException;
use tool\model\InstallModel;


class Install extends Controller
{

    public function __construct()
    {
        $file = Util::path(TOOL_DIR, '.installed');
        if (file_exists($file)) {
            return;
        }
    }

    /**
     * 检查目录可写
     * @param string $dir
     * @param array $info
     * @return bool
     */
    private function checkDir(string $dir, array &$info = []): bool
    {
        $dir = Util::path(ROOT_DIR, $dir);
        $rt = $this->isWritable($dir);
        if ($rt) {
            $info[] = "<span class=yes>YES</span>--- {$dir} 目录存在 可写。";
        } else {
            $info[] = "<span class=no>NO</span>--- {$dir} 目录权限不足，请修改目录读写权限。";
        }
        return $rt;
    }

    /**
     * 检查文件可写
     * @param string $file
     * @param array $info
     * @return bool
     */
    private function checkFile(string $file, array &$info = []): bool
    {
        $file = Util::path(ROOT_DIR, $file);
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

    /**
     * 检查目录可写
     * @param string $dirPath
     * @return bool
     */
    private function isWritable(string $dirPath): bool
    {
        $dirPath = Util::path($dirPath);
        Util::makeDir($dirPath);
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


    #[Method(act: 'index', method: Method::GET)]
    public function index()
    {
        $this->display('install/install.tpl');
    }

    #[Method(act: 'check', method: Method::GET)]
    public function check()
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
        $this->display('install/check.tpl');
    }

    private function execFile(string $file, string $charset)
    {
        #写入配置文件
        $file = Util::path(TOOL_DIR, $file);
        if (file_exists($file)) {
            $data = file_get_contents($file);
            $data = str_replace('@@charset', $charset, $data);
            DB::exec($data);
        }
    }

    /**
     * 安装数据库
     */
    #[Method(act: 'database', method: Method::GET | Method::POST)]
    public function database()
    {
        $form = Form::create(InstallModel::class, 'add');
        if ($this->isGet()) {
            $data = Config::get('db.*', [
                'db_host' => '127.0.0.1',
                'db_port' => 3306,
                'db_name' => '',
                'db_user' => 'root',
                'db_pwd' => '',
                'db_prefix' => 'sl_',
                'db_charset' => 'utf8mb4'
            ]);
            $form->setData($data);
            $this->assign('form', $form);
            $this->display('install/database.tpl');
            return;
        }

        if ($this->isPost()) {
            $values = $this->completeForm($form);
            try {
                $insFile = Util::path(TOOL_DIR, '.installed');
                if (file_exists($insFile)) {
                    $this->error('工具已经安装，不可重复安装项目');
                }
                //检查创建数据库
                $cfgFile = Util::path(ROOT_DIR, '/config/db.config.php');
                $db = new Mysql($values['db_host'], $values['db_port'], '', $values['db_user'], $values['db_pwd'], $values['db_prefix']);
                $db->exec('CREATE DATABASE if not exists `' . $values['db_name'] . '` DEFAULT CHARSET ' . $values['db_charset'] . ' COLLATE ' . $values['db_charset'] . '_general_ci');
                $code = '<?php return ' . var_export($values, TRUE) . ';';
                #写入配置文件
                if (!file_exists($cfgFile)) {
                    file_put_contents($cfgFile, $code);
                    $this->execFile('data/tool.sql', $values['db_charset']);
                    $this->execFile('data/web.sql', $values['db_charset']);
                }
                file_put_contents($insFile, TOOL_VERSION);
            } catch (DBException $exception) {
                $this->error('保存失败：' . $exception->getMessage());
            } catch (\Exception $exception) {
                $this->error('保存失败：' . $exception->getMessage());
            }
            $this->success('保存成功', ['back' => '/admin']);
        }
    }

}