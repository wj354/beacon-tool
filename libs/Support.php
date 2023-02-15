<?php


namespace tool\libs;

use beacon\core\Logger;
use beacon\core\Util;

#[\Attribute]
class Support
{
    public string $name;
    public array $types;

    public static array $typeMap = [
        'Text' => ['text' => '文本框 Text', 'data-types' => ['varchar(200)', 'text']],
        'Hidden' => ['text' => '隐藏域 Hidden', 'data-types' => ['int(11)', 'tinyint(4)', 'varchar(200)', 'float(18,2)', 'decimal(18,2)', 'double(18,2)', 'text', 'longtext', 'json'], 'data-default' => 'int'],
        'Check' => ['text' => '是否 Check', 'data-types' => ['tinyint(1)']],
        'Integer' => ['text' => '整数 Integer', 'data-types' => ['int(11)']],
        'Number' => ['text' => '小数 Number', 'data-types' => ['decimal(18,2)', 'float(18,2)', 'double(18,2)']],
        'Password' => ['text' => '密码框 Password', 'data-types' => ['varchar(200)']],
        'Color' => ['text' => '颜色选择 Color', 'data-types' => ['varchar(200)']],
        'Date' => ['text' => '日期格式 Date', 'data-types' => ['date', 'datetime', 'int(11)']],
        'Datetime' => ['text' => '时间格式 Datetime', 'data-types' => ['datetime', 'int(11)']],
        'Time' => ['text' => '时间 Time', 'data-types' => ['time', 'varchar(200)']],
        'Select' => ['text' => '下拉框 Select', 'data-types' => ['int(11)', 'varchar(100)', 'decimal(18,2)'], 'data-default' => 'int'],
        'DelaySelect' => ['text' => '异步下拉 DelaySelect', 'data-types' => ['int(11)', 'varchar(100)', 'decimal(18,2)'], 'data-default' => 'int'],
        'RadioGroup' => ['text' => '单选组 RadioGroup', 'data-types' => ['int(11)', 'varchar(100)', 'decimal(18,2)'], 'data-default' => 'int'],
        'CheckGroup' => ['text' => '多选组 CheckGroup', 'data-types' => ['varchar(200)', 'int(11)', 'text', 'longtext', 'json'], 'data-default' => 'json'],
        'Remote' => ['text' => '远程验证输入框 Remote', 'data-types' => ['varchar(200)', 'text']],
        'Linkage' => ['text' => '联动下拉 Linkage', 'data-types' => ['varchar(200)', 'text', 'json'], 'data-default' => 'json'],
        'Textarea' => ['text' => '备注型 Textarea', 'data-types' => ['text', 'varchar(200)', 'longtext'], 'data-default' => 'text'],
        'UpFile' => ['text' => '文件上传 UpFile', 'data-types' => ['varchar(200)', 'text', 'json']],
        'UpImage' => ['text' => '图片上传 UpImage', 'data-types' => ['varchar(300)', 'text', 'json']],
        'XhEditor' => ['text' => 'Xh编辑器 XhEditor', 'data-types' => ['text', 'longtext']],
        'Tinymce' => ['text' => 'tiny编辑器 Tinymce', 'data-types' => ['text', 'longtext']],
        'SelectDialog' => ['text' => '选择对话框 SelectDialog', 'data-types' => ['int(11)', 'varchar(200)', 'json', 'decimal(18,2)'], 'data-default' => 'int'],
        'MultiDialog' => ['text' => '多选对话框 MultiDialog', 'data-types' => ['text', 'varchar(200)', 'json'], 'data-default' => 'json'],
        'Line' => ['text' => '分割行 Line', 'data-types' => ['none']],
        'Label' => ['text' => '标签 Label', 'data-types' => ['none']],
        'Button' => ['text' => '按钮 Button', 'data-types' => ['none']],
        'Container' => ['text' => '多行容器 Container', 'data-types' => ['json'], 'data-default' => 'json'],
        'Single' => ['text' => '单行容器 Single', 'data-types' => ['json'], 'data-default' => 'json'],
        'Transfer' => ['text' => '穿梭框 Transfer', 'data-types' => ['int(11)', 'tinyint(4)', 'varchar(200)', 'float(18,2)', 'decimal(18,2)', 'double(18,2)', 'text', 'longtext', 'json']],
        'Telephone' => ['text' => '电话号码 Telephone', 'data-types' => ['varchar(200)']],
    ];

    public function __construct(string $name, array $types)
    {
        $this->name = $name;
        $this->types = $types;
    }

    /**
     * 添加支持类型
     * @param string $type
     * @param string $name
     * @param array $vtypes
     */
    public static function addType(string $type, string $name, array $vtypes)
    {
        self::$typeMap[$type] = ['text' => $name, 'data-types' => $vtypes];
    }

    public static function getTypeOption()
    {
        $options = [];
        foreach (self::$typeMap as $key => $item) {
            $options[] = ['value' => $key, 'text' => $item['text'], 'data-types' => $item['data-types']];
        }
        return $options;
    }

    public static function loadOtherType()
    {
        $paths = [];
        $comPsr4 = Util::path(ROOT_DIR, 'vendor/composer/autoload_psr4.php');
        if (file_exists($comPsr4)) {
            $data = require_once $comPsr4;
            if (isset($data['tool\\support\\'])) {
                foreach ($data['tool\\support\\'] as $dir) {
                    $paths[] = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $dir);
                }
            }
            if (isset($data['tool\\'])) {
                foreach ($data['tool\\'] as $dir) {
                    $dir = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $dir);
                    if ($dir == TOOL_DIR) {
                        continue;
                    }
                    $paths[] = Util::path($dir, 'support');
                }
            }
        }
        foreach ($paths as $path) {
            if (is_dir($path)) {
                $directory = new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS);
                $iterator = new \RecursiveIteratorIterator($directory, \RecursiveIteratorIterator::SELF_FIRST);
                /**@var \SplFileInfo $fileInfo */
                foreach ($iterator as $fileInfo) {
                    if ($fileInfo->isFile() && $fileInfo->getExtension() == 'php') {
                        $type = $fileInfo->getBasename('.php');
                        try {
                            $refClass = new \ReflectionClass('tool\\support\\' . $type);
                            $temp = $refClass->getAttributes(Support::class);
                            if (isset($temp[0])) {
                                /**@var Support $supper */
                                $supper = $temp[0]->newInstance();
                                Support::addType($type, $supper->name, $supper->types);
                            }
                        } catch (\ReflectionException) {
                            continue;
                        }
                    }
                }
            }
        }
    }
}