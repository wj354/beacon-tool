<?php


namespace tool\controller;


use beacon\core\Controller;
use beacon\core\DB;
use beacon\core\DBException;
use beacon\core\Request;
use beacon\core\Util;

class Base extends Controller
{
    /**
     * Base constructor.
     * @throws DBException
     */
    public function __construct()
    {
        $installed = $this->checkInstall();
        if (!$installed) {
            $this->redirect('~/install');
        }
        $adminId = Request::getSession('adminId:i', 0);
        if (!$adminId) {
            $this->redirect('^/admin');
        }
    }

    /**
     * @return bool
     * @throws DBException
     */
    private function checkInstall(): bool
    {
        $file = Util::path(TOOL_DIR, '.installed');
        if (file_exists($file)) {
            return true;
        }
        $db_config = Util::path(ROOT_DIR, 'config/db.config.php');
        if (file_exists($db_config)) {
            return DB::existsTable('@pf_tool_app');
        }
        return false;
    }


}
