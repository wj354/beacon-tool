<?php
/**
 * Created by PhpStorm.
 * User: wj008
 * Date: 18-11-26
 * Time: 上午3:56
 */

namespace tool;

define('TOOL_PATH', __DIR__);

use beacon\DB;
use beacon\DBException;
use beacon\Route;
use beacon\Utils;

class Tool
{
    public static $isInstall = false;

    public static function register()
    {
        Route::register('tool', [
            'path' => TOOL_PATH,
            'namespace' => 'tool',
            'base' => '/tool',
            'rules' => [
                '@^/r-([a-z0-9_-]+)-(js|css|png)$@i' => [
                    'ctl' => 'r',
                    'act' => 'index',
                    'f' => '$1.$2',
                ],
                '@^/(\w+)/(\w+)/(\d+)$@i' => [
                    'ctl' => '$1',
                    'act' => '$2',
                    'id' => '$3',
                ],
                '@^/(\w+)/(\d+)$@i' => [
                    'ctl' => '$1',
                    'act' => 'index',
                    'id' => '$2',
                ],
                '@^/(\w+)/(\w+)$@i' => [
                    'ctl' => '$1',
                    'act' => '$2',
                ],
                '@^/(\w+)/?$@i' => [
                    'ctl' => '$1',
                    'act' => 'index',
                ],
                '@^/$@' => [
                    'ctl' => 'index',
                    'act' => 'index',
                ],
            ],
            'resolve' => function ($ctl, $act, $keys) {
                if ($ctl == 'r' && !empty($keys['file'])) {
                    return '/r-' . $keys['file'];
                }
                $url = '/{ctl}';
                if (!empty($act) && $act != 'index') {
                    $url .= '/{act}';
                }
                if (isset($keys['id'])) {
                    $url .= '/{id}';
                }
                return $url;
            }
        ]);
        //没有设置安装的情况下
        if (!self::$isInstall) {
            $db_config = Utils::path(ROOT_DIR, 'config/db.config.php');
            if (file_exists($db_config)) {
                try {
                    self::$isInstall = DB::existsTable('@pf_tool_app');
                } catch (DBException $e) {
                }
            }
            if (!self::$isInstall) {
                Route::addCtlPrefix('tool', 'install\\');
            }
        }
    }

    public static function install()
    {
        $file = Utils::path(TOOL_PATH, 'data', 'install.sql');
        if (file_exists($file)) {
            $data = file_get_contents($file);
            DB::exec($data);
        }
    }

}