$(function () {


    $('#list').on('render', function (ev, source) {
        if (source && source['pageInfo']) {
            $('#records-count').text(source['pageInfo']['recordsCount'] || '0');
        }
        $('.reload').off('success');
        $('.reload').on('success', function (ev, ret) {
            Yee.readyDialog(function (dialog) {
                dialog.success(ret);
            });
            $('#list').trigger('reload');
        });
    });

    //排序
    $('#list').on('order', function (ev, data) {
        var form = $('#searchForm');
        var inp1 = form.find(':input[name=sort]');
        if (inp1.length == 0) {
            inp1 = $('<input type="hidden" name="sort"/>').appendTo(form);
        }
        inp1.val(data.value);
        $('#searchForm').submit();
    });

    $(document.body).on('click', 'a.v-copy', function (ev) {
        var btn = $(this);
        var input = btn.prev(':input');
        if (input.length > 0) {
            input[0].select(); // 选中文本
            document.execCommand("copy");
            Yee.msg('复制成功');
        }
        return false;
    });

    //如果对话框打开
    Yee.readyDialog(function (dialog) {
        $('.yee-wrap').addClass('yee-dialog');
    });

});
