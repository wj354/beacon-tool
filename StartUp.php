<?php

namespace tool;

use \beacon\core\Config;

define('TOOL_DIR', __DIR__);
define('TOOL_VERSION', '4.0.0');

class StartUp
{
    public static function init()
    {
        Config::append([
            'sdopx.template_dir' => [TOOL_DIR . '/view'],
        ]);
    }
}
