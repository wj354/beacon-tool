<?php


namespace tool\libs;


use beacon\core\App;
use beacon\core\Form;
use beacon\core\Util;

class Helper
{

    /**
     * 获取插件类名
     * @param string $name
     * @return string
     */
    public static function getSupportClassName(string $name): string
    {
        $class = 'tool\\support\\' . $name;
        if (!class_exists($class)) {
            return '';
        }
        return $class;
    }

    /**
     * @param string $name
     * @param string $type
     * @return ?Form
     */
    public static function getSupportForm(string $name, string $type = ''): ?Form
    {
        $typeClass = self::getSupportClassName($name);
        if (empty($typeClass)) {
            return null;
        }
        return Form::create($typeClass, $type);
    }

    /**
     * 转换为数组
     * @param array|string $string
     * @param array $def
     * @return array
     */
    public static function convertArray(array|string|null $string, array $def = []): array
    {
        if ($string === null) {
            return $def;
        }
        if (is_array($string)) {
            return $string;
        }
        $string = trim($string);
        if (empty($string)) {
            return $def;
        }
        if (!Util::isJson($string)) {
            return $def;
        }
        return json_decode($string, true);
    }


    public static function changeExtendField()
    {

    }


    public static function fixNamespace(string $namespace): string
    {
        $namespace = str_replace('/', '\\', $namespace);
        return trim($namespace, '\\');
    }

    public static function var(mixed $data, $sp = ''): string
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
                    $tabs[] = self::var($item, $sp);
                }
            } else {
                foreach ($data as $key => $item) {
                    $tabs[] = var_export($key, true) . ' => ' . self::var($item, $sp);
                }
            }
            $code = join(',', $tabs);
            if (trim($code) == '') {
                return '[]';
            }
            return '[' . join(',', $tabs) . ']';
        }
        //格式化的
        if ($isArr) {
            foreach ($data as $key => $item) {
                $tabs[] = $sp . '    ' . self::var($item, $sp . '    ');
            }
        } else {
            foreach ($data as $key => $item) {
                $tabs[] = $sp . '    ' . var_export($key, true) . ' => ' . self::var($item, $sp . '    ');
            }
        }
        $code = join(",", $tabs);
        if (trim($code) == '') {
            return '[]';
        }
        return "[\n" . join(",\n", $tabs) . "\n" . $sp . ']';
    }

    public static function tplUrl(string $url): string
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
}