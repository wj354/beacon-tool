<?php


namespace tool\controller;


use beacon\core\Controller;
use beacon\core\DB;
use beacon\core\Method;
use beacon\core\Util;

class Install extends Controller
{

    public function __construct()
    {
        $file = Util::path(TOOL_DIR, '.installed');
        if (file_exists($file)) {
            return;
        }
    }

    #[Method(act: 'index', method: Method::GET)]
    public function index()
    {
        $this->display('install/install.tpl');
    }

    #[Method(act: 'index', method: Method::POST)]
    public function confirm()
    {
        $file = Util::path(TOOL_DIR, 'data', 'install.sql');
        if (file_exists($file)) {
            $data = file_get_contents($file);
            DB::exec($data);
        }
        $file = Util::path(TOOL_DIR, '.installed');
        file_put_contents($file, TOOL_VERSION);
        $this->success('创建成功', ['back' => '/tool']);
    }

}