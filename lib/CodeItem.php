<?php
/**
 * Created by PhpStorm.
 * User: wj008
 * Date: 18-12-3
 * Time: 下午9:23
 */

namespace tool\lib;


class CodeItem
{
    private $_code = '';
    private $_use = [];

    public function setCode($code)
    {
        if (is_array($code)) {
            $this->_code = join("\n", $code);
        } else {
            $this->_code = $code;
        }
    }

    public function getCode()
    {
        return $this->_code;
    }

    public function use($name)
    {
        $this->_use[$name] = $name;
    }

    public function getUse()
    {
        return $this->_use;
    }
}