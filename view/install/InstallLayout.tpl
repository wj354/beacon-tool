<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <title>Beacon Tool Installer</title>
    <link type="text/css" rel="stylesheet" href="/yeeui/css/yeeui.css"/>
    <link type="text/css" rel="stylesheet" href="/icofont/icofont.css"/>
    <script src="/yeeui/third/jquery-3.3.1.min.js"></script>
    <script src="/yeeui/yee.js"></script>
    <link type="text/css" rel="stylesheet" href="{url ctl='r' file='install-css'}"/>
</head>
<body>
<div class="main-layout">
    <div class="main-title">Beacon Tool 2.0 安装</div>
    <form method="post" yee-module="validate ajax">
        <div class="form-area">
            <div class="form-left">
                <ul class="step">
                    <li {if $this->route('act')=='index'}class="active"{/if}>软件说明</li>
                    <li {if $this->route('act')=='check'}class="active"{/if}>安装环境检测</li>
                    <li {if $this->route('act')=='database'}class="active"{/if}>配置数据库</li>
                </ul>
            </div>
            <div class="form-right">
                {block name='content'}{/block}
            </div>
            <div style="clear:both;"></div>
            <div class="foot">
                <div class="foot-left">
                    wj008 (叶子 26029682@qq.com)
                </div>
                <div class="foot-right">
                    {block name='btn'}{/block}
                </div>
            </div>
        </div>
    </form>
</div>
</body>
</html>
