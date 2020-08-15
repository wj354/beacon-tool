<?php


namespace tool\lib;


use beacon\Config;
use beacon\Mysql;

class ToolDb
{
    static $dbMap = [];

    static function getDb(int $appId)
    {
        if (empty($appId)) {
            throw new \Exception("获取数据库信息失败");
        }
        if (isset(self::$dbMap[$appId])) {
            return self::$dbMap[$appId];
        }
        $row = DB::getRow("select * from @pf_tool_app where id=?", $appId);
        if (!$row) {
            throw new \Exception("获取数据库信息失败");
        }
        $host = empty($row['db_host']) ? Config::get("db_host") : $row['db_host'];
        $port = empty($row['db_port']) ? Config::get("db_port") : $row['db_port'];
        $name = empty($row['db_name']) ? Config::get("db_name") : $row['db_name'];
        $user = empty($row['db_user']) ? Config::get("db_user") : $row['db_user'];
        $pass = empty($row['db_pwd']) ? Config::get("db_pwd") : $row['db_pwd'];
        $prefix = empty($row['db_prefix']) ? Config::get("db_prefix") : $row['db_prefix'];
        $charset = empty($row['db_charset']) ? Config::get("db_charset") : $row['db_charset'];
        if (empty($charset)) {
            $charset = 'utf8';
        }
        $db = new Mysql($host, $port, $name, $user, $pass, $prefix, $charset);
        return self::$dbMap[$appId] = $db;
    }
}