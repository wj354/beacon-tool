$(function () {
    var cacheFields = [];
    var cacheTbName = '';
    var changeForm = function () {
        var that = $('#formId');
        var formId = $('#formId').val();
        $.post('/tool/lists/get_field', {formId: formId}, function (ret) {
            if (ret && ret.status) {
                cacheFields = ret.options;
                cacheTbName = ret.tbName;
                $('#tbName').val(cacheTbName);
                //更新字段列表
                $('.dbfield').each(function (idx, item) {
                    var instance = $(item).instance('delay-select');
                    if (instance) {
                        instance.update(cacheFields);
                    }
                });
            }
        }, 'json');
        var text = that.find(':selected').text();
        var optArr = text.split('|');
        if (optArr.length === 2) {
            var titleBox = $('#title');
            var keyBox = $('#key');
            if (titleBox.val() === '') {
                titleBox.val($.trim(optArr[0]));
            }
            if (keyBox.val() === '') {
                keyBox.val($.trim(optArr[1]));
            }
        }
    }
    $('#formId').on('change', changeForm);
    changeForm();
    //添加时设置字段选项值
    $('#fields').on('addItem', function (ev, item) {
        item.find('.dbfield').each(function (idx, item) {
            var instance = $(item).instance('delay-select');
            if (instance) {
                instance.update(cacheFields);
            }
        });
    });
    //字段选择
    $('#fields').on('change', 'select.dbfield', function (ev) {
        var emt = $(this);
        var val = emt.val();
        var text = emt.find("option:selected").text().split(' | ')[0];
        var parent = emt.parents('.container-item:first');
        var ticBox = parent.find(':input[name*="[title]"]');
        var valBox = parent.find(':input[name*="[code]"]');
        var widthBox = parent.find('input[name*="[thWidth]"]');
        var thAlign = parent.find(':input[name*="[thAlign]"]');
        var tdAlign = parent.find(':input[name*="[tdAlign]"]');
        var thFixed = parent.find(':input[name*="[keyName]"]');
        if (val !== '0' && val !== '') {
            if (ticBox.val() === '') {
                ticBox.val(text);
            }
            var oldVal = valBox.val();
            if (val == 'id') {
                widthBox.val(40);
            }
            if (val == 'name' || val == 'title') {
                widthBox.val('');
                thAlign.val('left');
                tdAlign.val('left');
            }
            if (val == 'cover' || val == 'img' || val == 'images' || val == 'photo') {
                valBox.val(oldVal + '<img src="{$rs.' + val + '}" height="40"/>');
            } else if (val == 'allow') {
                valBox.val(oldVal + '{if $rs.allow}<span class="green">正常</span>{else}<span class="gray">禁用</span>{/if}');
            } else if (val == 'lock') {
                valBox.val(oldVal + '{if $rs.lock}<span class="gray">锁定</span>{else}<span class="green">正常</span>{/if}');
            } else {
                valBox.val(oldVal + '{$rs.' + val + '}');
            }
            var fixedVal = thFixed.val() || '';
            if (fixedVal == '') {
                thFixed.val('_' + val);
            }
        }
    });
    $('#fields').on('click', 'a.order-btn', function (ev) {
        var parent = $(this).parents('.container-item:first');
        var orderBox = parent.find(':input[name*="[orderName]"]');
        var fieldBox = parent.find(':input[name*="[field]"]');
        var field = fieldBox.val() || '';
        if (field) {
            orderBox.val(field);
        }
    });

    $('#topButtons a[name="add-operate"]').on('success', function (ev, data) {
        var obj = Yee.instance('#topButtons', 'container');
        if (obj) {
            var item = obj.addItem();
            if (item) {
                item.find(':input[name*="[code]"]').val(data);
            }
        }
    });
    $('#selectButtons a[name="add-operate"]').on('success', function (ev, data) {
        var obj = Yee.instance('#selectButtons', 'container');
        if (obj) {
            var item = obj.addItem();
            if (item) {
                item.find(':input[name*="[code]"]').val(data);
            }
        }
    });
    $('#listButtons a[name="add-operate"]').on('success', function (ev, data) {
        var obj = Yee.instance('#listButtons', 'container');
        if (obj) {
            var item = obj.addItem();
            if (item) {
                item.find(':input[name*="[code]"]').val(data);
            }
        }
    });

    $('#row_actions').on('click', ':checkbox', function (ev) {
        $('#row_actions').find(':checkbox:checked').each(function (_, el) {
            var value = $(el).val() || '';
            if (value == 'add') {
                var hasAdd = false;
                $('#topButtons :input[name*="[code]"]').each(function (_, el) {
                    var code = $(el).val() || '';
                    if (/act='add'/.test(code)) {
                        hasAdd = true;
                    }
                });
                if (!hasAdd) {
                    var obj = Yee.instance('#topButtons', 'container');
                    if (obj) {
                        var item = obj.addItem();
                        if (item) {
                            item.find(':input[name*="[code]"]').val('<a href="{url act=\'add\'}"  class="yee-btn red"><i class="icofont-patient-file"></i>新增</a>');
                        }
                    }
                }
            } else if (value == 'edit' || value == 'delete' || value == 'toggleAllow') {
                var has = false;
                $('#listButtons :input[name*="[code]"]').each(function (_, el) {
                    var code = $(el).val() || '';
                    var reg = new RegExp("act='" + value + "'");
                    if (reg.test(code)) {
                        has = true;
                    }
                });
                if (!has) {
                    var obj = Yee.instance('#listButtons', 'container');
                    if (obj) {
                        var item = obj.addItem();
                        if (item) {
                            if (value == 'edit') {
                                item.find(':input[name*="[code]"]').val('<a href="{url act=\'edit\' id=$rs.id}" class="yee-btn blue-bd"><i class="icofont-pencil-alt-5"></i>编辑</a>');
                            }
                            if (value == 'delete') {
                                item.find(':input[name*="[code]"]').val('<a href="{url act=\'delete\' id=$rs.id}" yee-module="confirm ajax" data-confirm@msg="确定要删除该数据了吗？" class="yee-btn red-bd" on-success="$(\'#list\').emit(\'reload\');"><i class="icofont-bin"></i>删除</a>');
                            }
                            if (value == 'toggleAllow') {
                                item.find(':input[name*="[code]"]').val('<a href="{url act=\'toggleAllow\' id=$rs.id}" yee-module="ajax"  class="yee-btn" on-success="$(\'#list\').emit(\'reload\');">{if $rs.allow}<i class="icofont-not-allowed"></i>禁用{else}<i class="icofont-verification-check"></i>审核{/if}</a>');
                            }
                        }
                    }
                }
            } else if (value == 'deleteChoice' || value == 'allowChoice' || value == 'revokeChoice') {
                $('#useSelect').prop('checked', true);
                var has = false;
                $('#selectButtons :input[name*="[code]"]').each(function (_, el) {
                    var code = $(el).val() || '';
                    var reg = new RegExp("act='" + value + "'");
                    if (reg.test(code)) {
                        has = true;
                    }
                });
                if (!has) {
                    var obj = Yee.instance('#selectButtons', 'container');
                    if (obj) {
                        var item = obj.addItem();
                        if (item) {
                            if (value == 'deleteChoice') {
                                item.find(':input[name*="[code]"]').val('<a href="{url act=\'deleteChoice\'}" yee-module="confirm ajax choice" data-confirm@msg="确定要删除所选数据了吗？" class="yee-btn red-bd" on-success="$(\'#list\').emit(\'reload\');"><i class="icofont-bin"></i>删除所选</a>');
                            }
                            if (value == 'allowChoice') {
                                item.find(':input[name*="[code]"]').val('<a href="{url act=\'allowChoice\'}" yee-module="ajax choice" class="yee-btn" on-success="$(\'#list\').emit(\'reload\');"><i class="icofont-verification-check"></i>审核所选</a>');
                            }
                            if (value == 'revokeChoice') {
                                item.find(':input[name*="[code]"]').val('<a href="{url act=\'revokeChoice\'}" yee-module="ajax choice" class="yee-btn" on-success="$(\'#list\').emit(\'reload\');"><i class="icofont-not-allowed"></i>禁用所选</a>');
                            }
                        }
                    }
                }
            }
        });
    });


    //全选
    $('#select-all-btn').on('click', function () {
        $(':checkbox.select-item').prop('checked', $(this).prop('checked'));
    });
    //复制
    var copyFunc = function () {
        var sitem = $(':checkbox.select-item:checked');
        if (sitem.length == 0) {
            Yee.msg('没有选择任何要复制的栏目');
            return;
        }
        var dataItems = [];
        sitem.each(function () {
            var item = $(this).parents('.container-item:first');
            var data = {};
            data.title = item.find(':input[name*="[title]"]').val();
            data.orderName = item.find(':input[name*="[orderName]"]').val();
            data.thAlign = item.find(':input[name*="[thAlign]"]').val();
            data.thWidth = item.find(':input[name*="[thWidth]"]').val();
            data.tdAlign = item.find(':input[name*="[tdAlign]"]').val();
            data.tdAttrs = item.find(':input[name*="[tdAttrs]"]').val();
            data.field = item.find(':input[name*="[field]"]').val();
            data.keyName = item.find(':input[name*="[keyName]"]').val();
            data.code = item.find(':input[name*="[code]"]').val();
            dataItems.push(data);
        });
        if (window.clipboardData) {
            window.clipboardData.setData('text', JSON.stringify({type: 'listField', datas: dataItems}));
            Yee.msg('复制成功');
        } else if (window.localStorage) {
            window.localStorage.setItem('copyList', JSON.stringify({type: 'listField', datas: dataItems}));
            Yee.msg('复制成功');
        } else {
            Yee.msg('浏览器不支持复制');
        }
    };
    //黏贴
    var pasteFunc = function () {
        var ret = null;
        if (window.clipboardData) {
            ret = window.clipboardData.getData('text');
        } else if (window.localStorage) {
            ret = window.localStorage.getItem('copyList');
        }
        if (ret == null) {
            return;
        }
        try {
            ret = JSON.parse(ret);
            if (ret['type'] && ret['type'] == 'listField' && ret['datas']) {
                var dataItems = ret['datas'];
                var obj = Yee.instance('#fields', 'container');
                if (obj) {
                    for (var i = 0; i < dataItems.length; i++) {
                        var data = dataItems[i];
                        var item = obj.addItem();
                        if (item) {
                            (function (data, item) {
                                setTimeout(function () {
                                    item.find(':input[name*="[title]"]').val(data.title);
                                    item.find(':input[name*="[orderName]"]').val(data.orderName);
                                    item.find(':input[name*="[thAlign]"]').val(data.thAlign);
                                    item.find(':input[name*="[thWidth]"]').val(data.thWidth);
                                    item.find(':input[name*="[tdAlign]"]').val(data.tdAlign);
                                    item.find(':input[name*="[tdAttrs]"]').val(data.tdAttrs);
                                    var filed = item.find(':input[name*="[field]"]').val(data.field);
                                    filed.data('value', data.field);
                                    item.find(':input[name*="[keyName]"]').val(data.keyName);
                                    item.find(':input[name*="[code]"]').val(data.code);
                                }, 100);
                            })(data, item);
                        }
                    }
                }
            }
        } catch (e) {

        }
    };

    $(document.body).on('change', 'select.shortcut', function (ev) {
        var sbox = $(this);
        var val = sbox.val();
        if (val) {
            var p = sbox.parents('div.container-item:first');
            var tbox = p.find('textarea');
            tbox.val(sbox.val());
        }
    });

    $('#copy-btn').on('click', copyFunc);

    $('#delall-btn').on('click', function () {
        var sitem = $(':checkbox.select-item:checked');
        if (sitem.length == 0) {
            Yee.msg('没有选择任何要复制的栏目');
            return;
        }
        var idx = Yee.confirm('确定要删除字段了吗？', function () {
            Yee.close(idx);
            var dataItems = [];
            sitem.each(function () {
                var item = $(this).parents('.container-item:first');
                dataItems.push(item);
            });
            if (dataItems.length > 0) {
                for (var i = dataItems.length - 1; i >= 0; i--) {
                    var delitem = dataItems[i];
                    if (delitem) {
                        delitem.remove();
                    }
                }
                var obj = Yee.instance('#fields', 'container');
                if (obj) {
                    obj.updateIndex();
                }
            }
        });
    });

    $('#paste-btn').on('click', pasteFunc);

    $(document).on('paste', ':input', function (ev) {
        ev.stopPropagation();
    });
    $(document).on('copy', ':input', function (ev) {
        ev.stopPropagation();
    });
    $(document).on('paste', '#row_fields', pasteFunc);

    $(document).on('copy', '#row_fields', copyFunc);
});