<?php
/**
 * Created by PhpStorm.
 * User: wj008
 * Date: 2019/1/12
 * Time: 22:28
 */

namespace tool\controller;


use beacon\Logger;
use beacon\Utils;

class Upload extends BaseController
{
    public function indexAction()
    {
        $this->setContentType('json');
        if (!isset($_FILES['filedata'])) {
            $this->error('上传失败');
        }
        $file = $_FILES['filedata'];
        if (empty($file['name'])) {
            $this->error('上传失败');
        }
        if (empty($file['tmp_name'])) {
            $this->error('上传失败');
        }
        $info = pathinfo($file['name']);
        if (!($info['extension'] == 'form' || $info['extension'] == 'list')) {
            $this->error('文件类型不符，只能上传 form 或者 list');
        }
        $newFile = time() . '.' . $info['extension'];
        $path = Utils::path(ROOT_DIR, 'runtime/temp', $newFile);
        Utils::makeDir(dirname($path));
        if (!(move_uploaded_file($file["tmp_name"], $path) && file_exists($path))) {
            $this->error('上传失败');
        }
        $data = [];
        $data['url'] = $newFile;
        $data['localName'] = $newFile;
        $data['orgName'] = $newFile;
        $this->success("上传成功", ['data' => $data]);
    }
}