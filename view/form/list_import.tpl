<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>导入列表</title>
    <link type="text/css" rel="stylesheet" href="/yeeui/css/yeeui.css?v={time()}"/>
    <link type="text/css" rel="stylesheet" href="/icofont/icofont.css"/>
    <script src="/yeeui/third/jquery-3.3.1.min.js"></script>
    <script src="/yeeui/yee.js"></script>
</head>
<body>
<div class="yee-wrap" style="padding: 0; background:#fff;">
    <div style="padding:10px  50px 70px 50px">
        <div style="line-height: 30px;">上传列表文件</div>
        <div>
            <input id="upload" name="upload" style="width:300px" class="form-inp up-file" data-type="file"
                   data-extensions="list" data-field-name="filedata" data-url="/tool/upload" yee-module="upload"
                   type="text"/></div>
    </div>
</div>
{literal}
    <script>
        Yee.readyDialog(function (dialog) {
            $(".yee-wrap").addClass("yee-dialog");
            $(".yee-form-header").hide();
            $('#upload').on('uploadComplete', function (ev, ret) {
                if (ret.status) {
                    var url = ret.data.url;
                    dialog.success(url);
                    dialog.close();
                }
            });
        });
    </script>
{/literal}
</body>
</html>
