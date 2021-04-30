$(function () {

    function copyFunc() {
        var type = $(this).data('type') || 'field';
        var datatable = $('#list').instance('datatable');
        if (!datatable) {
            return null;
        }
        var fields = datatable.getCheckData('choice');
        if (fields.length == 0) {
            Yee.alert('复制失败，没有勾选任何选项');
            return;
        }
        if (window.clipboardData) {
            window.clipboardData.setData('text', JSON.stringify({type: type, fields: fields}));
            Yee.msg('复制成功');
        } else if (window.localStorage) {
            window.localStorage.setItem('copyText', JSON.stringify({type: type, fields: fields}));
            Yee.msg('复制成功');
        } else {
            Yee.msg('浏览器不支持复制');
        }
    }

    function pasteFunc() {
        var type = $(this).data('type') || 'field';
        var data = null;
        if (window.clipboardData) {
            data = window.clipboardData.getData('text');
        } else if (window.localStorage) {
            data = window.localStorage.getItem('copyText');
        }
        if (data == null) {
            return;
        }
        var url = $('#paste-btn').data('url') || '/tool/field/paste';
        try {
            data = JSON.parse(data);
            if (data['type'] && data['type'] == type && data['fields']) {
                if (type == 'field') {
                    var formId = $('#formId').val();
                    data['formId'] = formId;
                } else {
                    var listId = $('#listId').val();
                    data['listId'] = listId;
                }
                var idx = Yee.confirm('确定要黏贴字段了吗？', function () {
                    $.post(url, data, function (ret) {
                        if (ret && ret.status) {
                            Yee.msg(ret.msg);
                            $('#list').emit('reload');
                        } else {
                            Yee.msg(ret.msg);
                        }
                        Yee.close(idx);
                    }, 'json');
                });
            }
        } catch (e) {

        }
    }

    $('#copy-btn').on('click', copyFunc);
    $('#paste-btn').on('click', pasteFunc);
});