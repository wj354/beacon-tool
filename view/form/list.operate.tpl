<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>新增操作项</title>
    <link type="text/css" rel="stylesheet" href="/yeeui/css/yeeui.css?v={time()}"/>
    <link type="text/css" rel="stylesheet" href="/icofont/icofont.css"/>
    <script src="/yeeui/third/jquery-3.3.1.min.js"></script>
    <script src="/yeeui/yee.js"></script>
    <script src="{url ctl='Res' f="operate.js"}"></script>
</head>
<body>

<div class="yee-panel" style="padding-bottom: 0; margin-bottom: 0">
    <div class="yee-row">
        <label class="row-label" style="width:70px">文本:</label>
        <div class="row-cell">
            <textarea class="form-inp mf navy" id="text" style="width: 300px;"></textarea>
            <select class="form-inp" id="quick" style="vertical-align: top">
                <option value="">快捷设置</option>
                {if $this->get('type')=='add'}
                    <option value="add">新增</option>
                {/if}
                {if $this->get('type')=='list'}
                    <option value="add2">新增子项</option>
                    <option value="edit">编辑</option>
                    <option value="choice">选中</option>
                    <option value="allow">审核/禁用</option>
                    <option value="delete">删除</option>
                {/if}
                {if $this->get('type')=='select'}
                    <option value="allowChoice">选择审核</option>
                    <option value="revokeChoice">选择禁用</option>
                    <option value="deleteChoice">选择删除</option>
                {/if}
            </select>
        </div>
    </div>
    <div class="yee-row">
        <label class="row-label" style="width:70px">URL:</label>
        <div class="row-cell">
            <textarea class="form-inp mf navy" id="url" style="width: 400px;"></textarea>
        </div>
    </div>
    <div class="yee-row">
        <label class="row-label" style="width:70px">CSS类:</label>
        <div class="row-cell">
            <input class="form-inp mf navy" id="class" value="yee-btn"/> &nbsp; ICON:<input id="icon" class="form-inp mf navy"/>
        </div>
    </div>
    <div class="yee-row">
        <label class="row-label" style="width:70px">样式:</label>
        <div class="row-cell">
            <textarea class="form-inp mf navy" id="style" style="width: 400px;"></textarea>
        </div>
    </div>
    <div class="yee-row">
        <label class="row-label" style="width:70px">插件:</label>
        <div class="row-cell">
            <label class="check-group"><input type="checkbox" name="module" value="ajax" class="form-inp"/><span>ajax请求</span></label>
            <label class="check-group"><input type="checkbox" name="module" value="dialog" class="form-inp"/><span>对话框</span></label>
            <label class="check-group"><input type="checkbox" name="module" value="confirm" class="form-inp"/><span>询问</span></label>
            <label class="check-group"><input type="checkbox" name="module" value="choice" class="form-inp"/><span>choice 全选</span></label>
            <label class="check-group"><input type="checkbox" name="module" value="select-dialog" class="form-inp"/><span>选中回传</span></label>
        </div>
    </div>

    <div class="yee-row" id="row-dialog" style="display: none">
        <label class="row-label" style="width:80px">对话框:</label>
        <div class="row-cell">
            宽：<input class="form-inp snumber" id="width" value="1200"/> &nbsp; 高： <input id="height" value="800" class="form-inp snumber"/>
        </div>

    </div>
    <div class="yee-row" id="row-confirm" style="display: none">
        <label class="row-label" style="width:70px">询问:</label>
        <div class="row-cell">
            <textarea class="form-inp" id="confirm" style="width: 400px">确定要删除该数据了吗？</textarea>
        </div>
    </div>
    {literal}
        <div class="yee-row" id="row-select" style="display: none">
            <label class="row-label" style="width:70px">选中回传:</label>
            <div class="row-cell">
                值：<input class="form-inp mf navy" id="s-value" value="{$rs.id}"/>&nbsp; 文本： <input id="s-text" value="{$rs.name}" class="form-inp mf navy"/>
            </div>
        </div>
    {/literal}
    <div class="yee-row" id="row-success" style="display: none">
        <label class="row-label" style="width:70px">成功事件:</label>
        <div class="row-cell">
            <textarea class="form-inp mf navy" id="success" style="width: 400px">$('#list').emit('reload');</textarea>
        </div>
    </div>

    <div class="yee-submit" style="padding: 15px 0; display: block;">
        <div class="tr">
            <input type="button" id="submit" class="form-btn red" value="确定">
            <a id="closeBtn" href="javascript:;" class="form-btn back">关闭</a>
        </div>
    </div>
</div>

</body>
</html>