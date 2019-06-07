$(function () {

    var cacheOption = {};
    var typeCache = {};

    var dbtypeBox = $('#dbtype');

    var old_type_val = $(':input[name=type]:checked').val() || '';

    //设置类型
    var setType = function (item) {
        if (item && item.len) {
            $('#dblen').data('v@disabled', false);
            $('#row_dblen').show();
        } else {
            $('#dblen').data('v@disabled', true).val(0);
            $('#row_dblen').hide();
        }
        if (item && item.point) {
            $('#dbpoint').data('v@disabled', false);
            $('#row_dbpoint').show();
        } else {
            $('#dbpoint').data('v@disabled', true).val(0);
            $('#row_dbpoint').hide();
        }
    }

    var updateOption = function (elem, option, value) {
        var instance = elem.instance('delay-select');
        if (instance) {
            instance.update(option, value);
        }
    }

    dbtypeBox.on('change', function () {
        var dbtype = $(this).val();
        if (dbtype) {
            var item = cacheOption[dbtype] || null;
            setType(item);
            if (item && item.len) {
                $('#dblen').val(item.len);
            }
            if (item && item.point) {
                $('#dbpoint').val(item.point);
            }
        }
    });

    $('#dblen').on('blur', function () {
        var dbtype = dbtypeBox.val() || dbtypeBox.data('value');
        var item = cacheOption[dbtype] || null;
        typeCache[dbtype] = typeCache[dbtype] || {};
        if (item.len) {
            typeCache[dbtype].len = $(this).val();
        }
    });

    $('#dbpoint').on('blur', function () {
        var dbtype = dbtypeBox.val() || dbtypeBox.data('value');
        var item = cacheOption[dbtype] || null;
        typeCache[dbtype] = typeCache[dbtype] || {};
        if (item.point) {
            typeCache[dbtype].point = $(this).val();
        }
    });

    $(':input[name=type]').on('click', function () {
        var that = $(this);
        var type_options = that.data('bind');
        var val = that.val();

        if (type_options) {
            cacheOption = {};
            var option = [];
            $(type_options).each(function (idx, item) {
                var m = item.match(/(\w+)(?:\((\d+)(?:,(\d+))?\))?/);
                if (m) {
                    var opt = {value: m[1], len: m[2], point: m[3]};
                    if (typeCache[opt.value]) {
                        if (opt.len && typeCache[opt.value]['len']) {
                            opt.len = typeCache[opt.value]['len'];
                        }
                        if (opt.point && typeCache[opt.value]['point']) {
                            opt.point = typeCache[opt.value]['point'];
                        }
                    }
                    cacheOption[opt.value] = opt;
                    option.push(opt);
                }
            });

            if (option.length == 0) {
                option.push({value: 'none'});
            }

            var dbtype = dbtypeBox.data('value') || dbtypeBox.val() || '';

            //切换默认值
            var defItem = (function () {
                var def = that.data('default') || null;
                if (def) {
                    for (var i = 0; i < option.length; i++) {
                        if (option[i].value == def) {
                            return option[i];
                        }
                    }
                }
                if (val == 'hidden' && old_type_val != 'hidden') {
                    return option[0];
                }
                for (var i = 0; i < option.length; i++) {
                    if (option[i].value == dbtype) {
                        return option[i];
                    }
                }
                return option[0];
            })();
            //更新
            if (dbtype == null || dbtype == '' || (old_type_val != val && dbtype != defItem.value)) {
                dbtypeBox.data('value', defItem.value);
                dbtypeBox.val(defItem.value);
                setType(defItem);
                if (defItem.len) {
                    $('#dblen').val(defItem.len);
                }
                if (defItem.point) {
                    $('#dbpoint').val(defItem.point);
                }
                updateOption(dbtypeBox, option, defItem.value);
            } else {
                var item = cacheOption[dbtype] || null;
                if (item) {
                    setType(item);
                    typeCache[dbtype] = typeCache[dbtype] || {};
                    if (item.len) {
                        typeCache[dbtype].len = $('#dblen').val();
                    }
                    if (item.point) {
                        typeCache[dbtype].point = $('#dbpoint').val();
                    }
                }
                updateOption(dbtypeBox, option, dbtype);
            }
        } else {
            updateOption(dbtypeBox, [], null);
        }

        if (old_type_val != val) {
            var extendLayout = $('#row_extend');
            var url = extendLayout.data('url') || '/tool/field/widget';
            var inputs = extendLayout.find(':input');
            inputs.push(that.get(0));
            var idBox = $(':input[name=id]');
            if (idBox.length > 0) {
                inputs.push(idBox.get(0));
            }
            var data = inputs.serialize();
            $.post(url, data, function (ret) {
                if (ret.status) {
                    extendLayout.html(ret.data || '');
                    Yee.update(extendLayout);
                } else {
                    extendLayout.empty();
                }
            }, 'json');
        }
        old_type_val = val;
    });

    $(':input[name=type]:checked').emit('click');
    $('#row_dynamic').on('click', 'a.show-field', function () {
        var abtn = $(this);
        abtn.off('success');
        var inp = $(this).prev(':input');
        abtn.one('success', function (ev, ret) {
            inp.val(ret);
        });
        var formId = $(':input[name=formId]').val();
        var ofield = inp.val();
        var url = Yee.url(abtn.attr('href'), {ofield: ofield, formId: formId});
        Yee.dialog(url, '选择字段', {
            width: 600,
            height: 800
        }, window, abtn);
        return false;
    });


    $(document.body).on('click', 'a.opt-copy', function (ev) {
        var btn = $(this);
        var wrap = btn.parents('div.option-container:first');
        var data = [];
        wrap.find('div.container-item').each(function (_, el) {
            var elitem = $(el);
            var key = elitem.find(':input[name*=value]').val() || null;
            var value = elitem.find(':input[name*=text]').val() || '';
            if (key !== null && key !== '') {
                var itemstr = "'" + key.replace(/'/g, "\\'") + "'=>'" + value.replace(/'/g, "\\'") + "'";
                data.push(itemstr);
            }
        });
        var input = btn.prev(':input');
        if (input.length > 0) {
            input.val('[' + data.join(',') + ']');
            input[0].select(); // 选中文本
            document.execCommand("copy");
            Yee.msg('复制成功');
        }
        return false;
    });
});