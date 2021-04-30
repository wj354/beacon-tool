<?php


namespace tool\controller;


use beacon\core\DB;
use beacon\core\DBException;

class AppBase extends Base
{
    public int $appId = 0;

    public function __construct()
    {
        parent::__construct();
        $appId = $this->param('appId:s', '');
        if ($appId === '') {
            $row = DB::getRow('select id from @pf_tool_app order by isDefault desc,id desc limit 0,1');
            if ($row == null) {
                $this->appId = 0;
            } else {
                $this->appId = intval($row['id']);
            }
        } else {
            $this->appId = intval($appId);
        }
    }

}