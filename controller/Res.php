<?php


namespace tool\controller;


use beacon\core\Controller;
use beacon\core\Method;
use beacon\core\Request;
use beacon\core\Util;

class Res extends Controller
{
    #[Method(act: 'index', method: Method::GET)]
    public function index(string $f = '')
    {
        if (empty($f)) {
            return '';
        }
        if (!preg_match('@^[a-z0-9_-]+\.(js|png|css)$@i', $f, $m)) {
            return '';
        }
        $path = Util::path(TOOL_DIR, 'res', $f);
        if (file_exists($path)) {
            Request::setContentType($m[1]);
            $myFile = fopen($path, "r");
            echo fread($myFile, filesize($path));
            fclose($myFile);
            exit;
        }
    }
}