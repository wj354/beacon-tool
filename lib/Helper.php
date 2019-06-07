<?php
/**
 * Created by PhpStorm.
 * User: wj008
 * Date: 18-12-3
 * Time: 下午8:42
 */

namespace tool\lib;


use beacon\Form;
use beacon\Route;
use beacon\Utils;

class Helper
{
    /**
     * 获取插件类名
     * @param string $type
     * @return string|null
     */
    public static function getWidgetClassName(string $type)
    {
        $typeClass = Route::getNamespace() . '\\widget\\' . Utils::toCamel($type);
        if (!class_exists($typeClass)) {
            return null;
        }
        return $typeClass;
    }

    /**
     * @param string $type
     * @param string $formType
     * @return Form
     */
    public static function getWidgetForm(string $type, string $formType = '')
    {
        $typeClass = self::getWidgetClassName($type);
        if ($typeClass == null) {
            return null;
        }
        $form = new $typeClass($formType);
        return $form;
    }

    /**
     * 格式化输出数据
     * @param $data
     * @param string $sp
     * @return string
     */
    public static function export($data, $sp = '')
    {
        if (!is_array($data)) {
            return var_export($data, true);
        }
        $tabs = [];
        $isArr = true;
        $idx = 0;
        foreach ($data as $key => $item) {
            if (strval($key) != strval($idx)) {
                $isArr = false;
                break;
            }
            $idx++;
        }
        //紧凑的
        if ($sp == 'c') {
            if ($isArr) {
                foreach ($data as $key => $item) {
                    $tabs[] = self::export($item, $sp);
                }
            } else {
                foreach ($data as $key => $item) {
                    $tabs[] = var_export($key, true) . ' => ' . self::export($item, $sp);
                }
            }
            return '[' . join(',', $tabs) . ']';
        }
        //格式化的
        if ($isArr) {
            foreach ($data as $key => $item) {
                $tabs[] = $sp . '    ' . self::export($item, $sp . '    ');
            }
        } else {
            foreach ($data as $key => $item) {
                $tabs[] = $sp . '    ' . var_export($key, true) . ' => ' . self::export($item, $sp . '    ');
            }
        }
        return "[\n" . join(",\n", $tabs) . "\n" . $sp . ']';
    }

    /**
     * 转换为数组
     * @param $string
     * @param array|null $def
     * @return array|mixed|string
     */
    public static function convertArray($string, array $def = null)
    {
        if (is_array($string)) {
            return $string;
        }
        $string = trim($string);
        if (empty($string)) {
            return $def;
        }
        if (!Utils::isJson($string)) {
            return $def;
        }
        return json_decode($string, true);
    }

    /**
     * 支持内置url
     * @param string $url
     * @return string|CodeItem|null
     */
    public static function convertUrl(string $url)
    {
        $url = trim($url);
        if (empty($url)) {
            return null;
        }
        if ($url[0] == '~' || $url[0] == '^') {
            $code = new CodeItem();
            $code->use('beacon\Route');
            $code->setCode('Route::url(' . var_export($url, true) . ')');
            return $code;
        }
        return $url;
    }

    public static function tplUrl(string $url)
    {
        $url = trim($url);
        if (empty($url)) {
            return '';
        }
        if ($url[0] == '~' || $url[0] == '^') {
            return '{url path=' . var_export($url, true) . '}';
        }
        return $url;
    }

    /**
     * 转换函数
     * @param string $func
     * @return CodeItem|null
     */
    public static function convertFunc(string $func)
    {
        $func = trim($func);
        if (empty($func)) {
            return null;
        }
        $codeItem = new CodeItem();
        $code = 'function(){ if(is_callable(' . var_export($func, true) . ')){return ' . $func . '();} return null;}';
        $codeItem->setCode($code);
        return $codeItem;
    }


    /**
     * 转换配置
     * @param string $config
     * @return null
     */
    public static function convertConfig(string $config)
    {
        $config = trim($config);
        if (empty($config)) {
            return null;
        }
        $codeItem = new CodeItem();
        $codeItem->use('beacon\Config');
        $codeItem->setCode('Config::get(' . var_export($config, true) . ')');
    }
}