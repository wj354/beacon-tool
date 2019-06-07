<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>选择数据库字段</title>
    <link type="text/css" rel="stylesheet" href="/yeeui/css/yeeui.css?v={time()}"/>
    <link type="text/css" rel="stylesheet" href="/icofont/icofont.css"/>
    <script src="/yeeui/third/jquery-3.3.1.min.js"></script>
    <script src="/yeeui/yee.js"></script>
</head>
<body>

<div class="yee-wrap yee-dialog">
    <div class="yee-list-header">
        <div class="yee-caption"><i class="icofont-listine-dots"></i> 选择数据库字段</div>
        <div class="yee-toolbar">
            <a id="add-btn" href="javascript:;" class="yee-btn red"><i class="icofont-patient-file"></i>确认选择</a>
        </div>
    </div>
    <div style="overflow: auto;max-height: 680px;padding-bottom: 3px;">
        <table class="yee-datatable" width="100%">
            <thead>
            <tr>
                <th width="40" align="center"><input class="form-inp check check-all" onclick="$('.check-item').prop('checked',this.checked);" type="checkbox"></th>
                <th>字段名</th>
                <th>类型</th>
                <th>备注</th>
            </tr>
            </thead>
            <tbody>
            {foreach from=$list item="rs"}
                <tr onmouseenter="this.className='hover'" onmouseleave="this.className=''">
                    <td align="center"><input class="form-inp check check-item" type="checkbox" {if $rs.checked}checked="checked"{/if} value="{$rs.Field}"></td>
                    <td>{$rs.Field}</td>
                    <td>{$rs.Type}</td>
                    <td>{$rs.Comment}</td>
                </tr>
            {/foreach}
            </tbody>
        </table>
    </div>
</div>
{literal}
    <script>
        Yee.readyDialog(function (dialog) {
            $('#add-btn').on('click', function () {
                var item = [];
                $('.check-item:checked').each(function () {
                    item.push($(this).val());
                });
                dialog.success(item.join(','));
                dialog.close();
            });
        });
    </script>
{/literal}
</body>
</html>