<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{block name="title"}{/block}</title>
    <link type="text/css" rel="stylesheet" href="/yeeui/css/yeeui.css"/>
    <link type="text/css" rel="stylesheet" href="/icofont/icofont.css"/>
    <script src="/yeeui/third/jquery-3.3.1.min.js"></script>
    <script src="/yeeui/yee.js"></script>
    <script src="{url ctl='Res' f="list.js"}"></script>
    {block name='header'}{/block}
</head>
<body>
<div class="yee-wrapper">
    <div class="yee-wrap">
        {block name='list-header'}{/block}
        {block name='list-tab'}{/block}
        {block name='list-attention'}{/block}
        <div class="yee-list-main">
            {block name='list-search'}{/block}
            <div class="yee-list">
                {block name='list-table'}{/block}
                {block name='list-pagebar'}{/block}
            </div>
            {block name='list-information'}{/block}
        </div>
    </div>
</div>
{block name='footer'}{/block}
</body>
</html>