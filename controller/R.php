<?php
/**
 * Created by PhpStorm.
 * User: wj008
 * Date: 18-11-26
 * Time: 上午3:30
 */

namespace tool\controller;

use beacon\Logger;
use beacon\Request;
use beacon\Route;
use beacon\Utils;

class R
{
    public function indexAction($f = null)
    {

        if ($f == null || !preg_match('@^[a-z0-9_-]+\.(js|png|css)$@i', $f, $m)) {
            return null;
        }
        $path = Utils::path(Route::getPath(), 'rc', $f);
        if (file_exists($path)) {
            Request::setContentType($m[1]);
            $myFile = fopen($path, "r");
            echo fread($myFile, filesize($path));
            fclose($myFile);
            exit;
        }
    }
}