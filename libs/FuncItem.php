<?php
/**
 * Created by PhpStorm.
 * User: wj008
 * Date: 18-12-3
 * Time: 下午9:23
 */

namespace tool\libs;


class FuncItem
{
    private string $_name = '';
    private string $_code = '';
    private array $_use = [];

    public function __construct(string $name)
    {
        $this->_name = $name;
    }

    public function setCode($code)
    {
        if (is_array($code)) {
            $this->_code = join("\n", $code);
        } else {
            $this->_code = $code;
        }
    }

    public function getName(): string
    {
        return $this->_name;
    }

    public function getCode(): string
    {
        return $this->_code;
    }

    public function use(string $name)
    {
        $this->_use[$name] = $name;
    }

    public function getUse(): array
    {
        return $this->_use;
    }
}