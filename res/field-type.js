$(function () {

    var cacheOption = {};
    var typeCache = {};

    var dbTypeBox = $('#dbType');

    var old_type_val = $(':input[name=type]:checked').val() || '';

    //设置类型
    var setType = function (item) {
        if (item && item.len) {
            Yee.dynamic('show', 'dbLen');
        } else {
            Yee.dynamic('hide', 'dbLen');
        }
        if (item && item.point) {
            Yee.dynamic('show', 'dbPoint');
        } else {
            Yee.dynamic('hide', 'dbPoint');
        }
    }

    var updateOption = function (elem, option, value) {
        var instance = elem.instance('delay-select');
        if (instance) {
            instance.update(option, value);
        }
    }

    dbTypeBox.on('change', function () {
        var dbType = $(this).val();
        if (dbType) {
            var item = cacheOption[dbType] || null;
            setType(item);
            if (item && item.len) {
                $('#dbLen').val(item.len);
            }
            if (item && item.point) {
                $('#dbPoint').val(item.point);
            }
        }
    });

    $('#dbLen').on('blur', function () {
        var dbType = dbTypeBox.val() || dbTypeBox.data('value');
        var item = cacheOption[dbType] || null;
        typeCache[dbType] = typeCache[dbType] || {};
        if (item.len) {
            typeCache[dbType].len = $(this).val();
        }
    });

    $('#dbPoint').on('blur', function () {
        var dbType = dbTypeBox.val() || dbTypeBox.data('value');
        var item = cacheOption[dbType] || null;
        typeCache[dbType] = typeCache[dbType] || {};
        if (item.point) {
            typeCache[dbType].point = $(this).val();
        }
    });

    $(':input[name=type]').on('click', function () {
        var that = $(this);
        var type_options = that.data('types');
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

            var dbType = dbTypeBox.data('value') || dbTypeBox.val() || '';

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
                    if (option[i].value == dbType) {
                        return option[i];
                    }
                }
                return option[0];
            })();
            //更新
            if (dbType == null || dbType == '' || (old_type_val != val && dbType != defItem.value)) {
                dbTypeBox.data('value', defItem.value);
                dbTypeBox.val(defItem.value);
                setType(defItem);
                if (defItem.len) {
                    $('#dbLen').val(defItem.len);
                }
                if (defItem.point) {
                    $('#dbPoint').val(defItem.point);
                }
                updateOption(dbTypeBox, option, defItem.value);
            } else {
                var item = cacheOption[dbType] || null;
                if (item) {
                    setType(item);
                    typeCache[dbType] = typeCache[dbType] || {};
                    if (item.len) {
                        typeCache[dbType].len = $('#dbLen').val();
                    }
                    if (item.point) {
                        typeCache[dbType].point = $('#dbPoint').val();
                    }
                }
                updateOption(dbTypeBox, option, dbType);
            }
        } else {
            updateOption(dbTypeBox, [], null);
        }

        if (old_type_val != val) {

            var extendLayout = $('#single-extend');
            var url = extendLayout.data('url') || '/tool/app_field/support';
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
                    Yee.render(extendLayout);
                } else {
                    extendLayout.empty();
                }
            }, 'json');

        }
        old_type_val = val;
    });

    $(':input[name=type]:checked').emit('click');
    $('#row_dynamic').on('click', 'a.show-field', function () {
        var aBtn = $(this);
        aBtn.off('success');
        var inp = $(this).prev(':input');
        aBtn.one('success', function (ev, ret) {
            inp.val(ret);
        });
        var formId = $(':input[name=formId]').val();
        var choice = inp.val() || '';
        var url = Yee.url(aBtn.attr('href'), {choice: choice, formId: formId});
        Yee.dialog(url, '选择字段', {
            width: 600,
            height: 800
        }, window, aBtn);
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