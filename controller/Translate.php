<?php


namespace tool\controller;


use beacon\core\Controller;
use beacon\core\Method;
use beacon\core\Util;

class Translate extends Controller
{
    #[Method(act: 'index', method: Method::GET | Method::POST)]
    public function index(string $text = '')
    {
        if (empty($text)) {
            $this->error('翻译失败');
        }
        $url = "https://translate.google.cn/translate_a/single?client=at&dt=t&dj=1&hl=es-ES&ie=UTF-8&oe=UTF-8&inputm=2&otf=2&iid=1dd3b944-fa62-4b55-b330-74909a99969e";
        $fields = array(
            'sl' => 'zh-CN',
            'tl' => 'en',
            'q' => urlencode($text)
        );
        if (strlen($fields['q']) >= 5000) {
            $this->error("Maximum number of characters exceeded: 5000");
        }
        $fields_string = "";
        foreach ($fields as $key => $value) {
            $fields_string .= $key . '=' . $value . '&';
        }
        rtrim($fields_string, '&');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, count($fields));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_ENCODING, 'UTF-8');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'AndroidTranslate/5.3.0.RC02.130475354-53000263 5.1 phone TRANSLATE_OPM5_TEST_1');
        $ret = curl_exec($ch);
        curl_close($ch);
        $a = json_decode($ret, 1);
        if (empty($a) || empty($a['sentences']) || empty($a['sentences'][0]) || empty($a['sentences'][0]['trans'])) {
            $this->error('翻译失败');
        }
        $word = strtolower(preg_replace('@[^A-Za-z]+@', '_', $a['sentences'][0]['trans']));
        $word = preg_replace('@_+@', '_', $word);
        $word = Util::toCamel($word);
        $this->success('翻译成功', ['camel' => $word, 'camel2' => lcfirst($word), 'under' => Util::toUnder($word)]);
    }
}