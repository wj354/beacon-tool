(function ($, Yee) {

    var config = {
        required: {
            text: '必填项', def: '{label}不可为空',
            ntype: {
                'radio-group': '请选择{label}',
                'select': '请选择{label}',
                'check-group': '请选择{label}',
                'up-file': '请上传{label}',
                'up-image': '请上传{label}'
            }
        },
        email: {text: '邮箱格式', def: '{label}格式不正确'},
        mobile: {text: '手机号码', def: '{label}格式不符'},
        idcard: {text: '身份证号码', def: '{label}格式不符'},
        number: {text: '数值形式', def: '{label}只能输入数值'},
        integer: {text: '整数形式', def: '{label}只能输入整数'},
        date: {text: '时间日期格式', def: '{label}格式错误'},
        min: {text: '最小范围', def: '{label}不能低于{0}'},
        max: {text: '最大范围', def: '{label}不能大于{0}'},
        range: {text: '区间范围', def: '{label}需在{0}-{1}之间'},
        minlength: {text: '最小长度', def: '{label}字数长度不足{0}位'},
        maxlength: {text: '最大长度', def: '{label}字数长度超出{0}位'},
        rangelength: {text: '长度区间', def: '{label}字数长度需在{0}-{1}之间'},
        money: {text: '金额格式', def: '{label}格式不正确'},
        url: {text: 'URL格式', def: '{label}格式不正确'},
        equal: {text: '与给定值相等', def: '{label}不符要求'},
        notequal: {text: '不能与给定值相等', def: '{label}不符要求'},
        equalto: {text: '与控件值相等', def: '两次输入的{label}不一致'},
        regex: {text: '正则表达式', def: '{label}格式不符'},
    };

    function ToolValidtor(element) {
        var that = $(element).hide();
        var valdBox = $('<div class="valdbox"></div>').insertAfter(that);
        var showBox = $('<div></div>').insertAfter(that);
        //添加下拉框
        var select = $('<select class="form-inp select"><select>').appendTo(showBox);
        for (var key in config) {
            var it = config[key];
            var item = $('<option value="' + key + '">' + it.text + '|' + key + '</option>');
            select.append(item);
        }
        var argsSpan = $('<span></span>').appendTo(showBox);

        var addBtn = $('<a style="margin-left:10px;" class="form-inp button" href="#">添加</a>').appendTo(showBox);
        $('<a style="margin-left:10px;" class="form-inp button" href="#">显示</a>').appendTo(showBox).on('click', function () {
            if ($(this).text() === '显示') {
                that.show();
                $(this).text('隐藏');
                return false;
            } else {
                that.hide();
                $(this).text('显示');
                return false;
            }
        });
        //更新到输入框
        var update = function () {
            var alldat = {};
            var items = valdBox.find('div.valid_item');
            if (items.length === 0) {
                that.val('');
            } else {
                items.each(function (index, element) {
                    var itdat = $(element).data('itdat');
                    alldat = $.extend(alldat, itdat);
                });
                that.data('alldat', alldat);
                $(that).triggerHandler('update');
                that.val(JSON.stringify(alldat));
            }
        };

        //更新图层
        var updateDiv = function () {
            valdBox.empty();
            try {
                var boxval = that.val();
                var boxdata = JSON.parse(boxval);
                console.log(boxdata);
                if (typeof (boxdata) === 'object') {
                    for (var key in boxdata) {
                        addType(key, boxdata[key]);
                    }
                }
            } catch (ex) {
            }
        };

        var tipType = function (type) {
            var label = $('#label').val();
            var ntype = $(':input[name=type]:checked').val();
            var msg = config[type] && config[type].def || '{label}格式不符';
            if (config[type] && config[type].ntype && config[type].ntype[ntype]) {
                msg = config[type].ntype[ntype];
            }
            msg = msg.replace('{label}', label);
            //console.log(msg);
            that.triggerHandler('additem', [{type: type, vals: msg}]);
        };
        //添加验证类型
        var addType = function (type, vals) {
            var items = valdBox.find('div.valid_item');
            var canadd = true;
            items.each(function () {
                var itemd = $(this);
                var itdat = itemd.data('itdat');
                if (itdat[type]) {
                    itdat[type] = vals;
                    itemd.data('itdat', itdat);
                    itemd.find('span').text(JSON.stringify(itdat));
                    canadd = false;
                    update();
                    return false;
                }
            });

            if (!canadd) {
                return;
            }
            var itemd = $('<div style="line-height:20px;" class="valid_item"></div>').appendTo(valdBox);
            var itdat = {};
            itdat[type] = vals;
            tipType(type);
            $('<span></span>').text(JSON.stringify(itdat)).appendTo(itemd);
            $('<a style="margin-left:10px;" href="#">删除</a>').one('click', function () {
                var p = $(this).parent();
                var itdat = p.data('itdat');
                var type = '';
                for (var i in itdat) {
                    type = i;
                    break;
                }
                p.remove();
                update();
                that.triggerHandler('delitem', [type]);
                return false;
            }).appendTo(itemd);
            itemd.data('itdat', itdat);
            update();
        };

        select.change(function () {
            var type = $(this).val();
            addBtn.off('click');
            argsSpan.empty();
            switch (type) {
                case 'min':
                    $('<span style="margin-left:10px;">最小范围：</span>').appendTo(argsSpan);
                    var mt1 = $('<input  type="text" class="form-inp integer"/>').appendTo(argsSpan);
                    $('<span style="margin-left:10px;">包括最小：</span>').appendTo(argsSpan);
                    var mt2 = $('<input type="checkbox">').appendTo(argsSpan);
                    addBtn.on('click', function () {
                        var val1 = mt1.val();
                        if (!/^[\-\+]?((\d+(\.\d*)?)|(\.\d+))$/.test(val1)) {
                            alert('最小范围必须是数字形式的数值！');
                            mt1.focus();
                            return false;
                        }
                        if (mt2.is(':checked')) {
                            addType(type, [Number(val1), true]);
                        } else {
                            addType(type, Number(val1));
                        }
                        return false;
                    });
                    break;
                case 'max':
                    $('<span style="margin-left:10px;">最大范围：</span>').appendTo(argsSpan);
                    var mt1 = $('<input  type="text" class="form-inp integer"/>').appendTo(argsSpan);
                    $('<span style="margin-left:10px;">包括最大：</span>').appendTo(argsSpan);
                    var mt2 = $('<input type="checkbox">').appendTo(argsSpan);
                    addBtn.on('click', function () {
                        var val1 = mt1.val();
                        if (!/^[\-\+]?((\d+(\.\d*)?)|(\.\d+))$/.test(val1)) {
                            alert('最大范围必须是数字形式的数值！');
                            mt1.focus();
                            return false;
                        }
                        if (mt2.is(':checked')) {
                            addType(type, [Number(val1), true]);
                        } else {
                            addType(type, Number(val1));
                        }
                        return false;
                    });
                    break;
                case 'minlength':
                    $('<span style="margin-left:10px;">最小长度：</span>').appendTo(argsSpan);
                    var mt1 = $('<input  type="text" class="form-inp integer"/>').appendTo(argsSpan);
                    $('<span style="margin-left:10px;">包括最小：</span>').appendTo(argsSpan);
                    var mt2 = $('<input type="checkbox">').appendTo(argsSpan);
                    addBtn.on('click', function () {
                        var val1 = mt1.val();
                        if (!/^[0-9]+$/.test(val1)) {
                            alert('最小长度必须是数字形式的数值！');
                            mt1.focus();
                            return false;
                        }
                        if (mt2.is(':checked')) {
                            addType(type, [Number(val1), true]);
                        } else {
                            addType(type, Number(val1));
                        }
                        return false;
                    });
                    break;
                case 'maxlength':
                    $('<span style="margin-left:10px;">最大长度：</span>').appendTo(argsSpan);
                    var mt1 = $('<input  type="text" class="form-inp integer"/>').appendTo(argsSpan);
                    $('<span style="margin-left:10px;">包括最大：</span>').appendTo(argsSpan);
                    var mt2 = $('<input type="checkbox">').appendTo(argsSpan);
                    addBtn.on('click', function () {
                        var val1 = mt1.val();
                        if (!/^[0-9]+$/.test(val1)) {
                            alert('最大长度必须是数字形式的数值！');
                            mt1.focus();
                            return false;
                        }
                        if (mt2.is(':checked')) {
                            addType(type, [Number(val1), true]);
                        } else {
                            addType(type, Number(val1));
                        }
                        return false;
                    });
                    break;
                case 'range':
                    $('<span style="margin-left:10px;">最小范围：</span>').appendTo(argsSpan);
                    var mt1 = $('<input  type="text" class="form-inp integer"/>').appendTo(argsSpan);
                    $('<span style="margin-left:10px;">最大范围：</span>').appendTo(argsSpan);
                    var mt2 = $('<input  type="text" class="form-inp integer"/>').appendTo(argsSpan);
                    $('<span style="margin-left:10px;">包括范围：</span>').appendTo(argsSpan);
                    var mt3 = $('<input type="checkbox">').appendTo(argsSpan);
                    addBtn.on('click', function () {
                        var val1 = mt1.val();
                        var val2 = mt2.val();
                        if (!/^[\-\+]?((\d+(\.\d*)?)|(\.\d+))$/.test(val1)) {
                            alert('最小范围必须是数字形式的数值！');
                            mt1.focus();
                            return false;
                        }
                        if (!/^[\-\+]?((\d+(\.\d*)?)|(\.\d+))$/.test(val2)) {
                            alert('最小范围必须是数字形式的数值！');
                            mt2.focus();
                            return false;
                        }
                        if (mt3.is(':checked')) {
                            addType(type, [Number(val1), Number(val2), true]);
                        } else {
                            addType(type, [Number(val1), Number(val2)]);
                        }
                        return false;
                    });
                    break;
                case 'rangelength':
                    $('<span style="margin-left:10px;">最小长度：</span>').appendTo(argsSpan);
                    var mt1 = $('<input  type="text" class="form-inp integer"/>').appendTo(argsSpan);
                    $('<span style="margin-left:10px;">最大长度：</span>').appendTo(argsSpan);
                    var mt2 = $('<input  type="text" class="form-inp integer"/>').appendTo(argsSpan);
                    $('<span style="margin-left:10px;">包括范围：</span>').appendTo(argsSpan);
                    var mt3 = $('<input type="checkbox">').appendTo(argsSpan);
                    addBtn.on('click', function () {
                        var val1 = mt1.val();
                        var val2 = mt2.val();
                        if (!/^[0-9]+$/.test(val1)) {
                            alert('最小长度必须是数字形式的数值！');
                            mt1.focus();
                            return false;
                        }
                        if (!/^[0-9]+$/.test(val2)) {
                            alert('最小长度必须是数字形式的数值！');
                            mt2.focus();
                            return false;
                        }
                        if (mt3.is(':checked')) {
                            addType(type, [Number(val1), Number(val2), true]);
                        } else {
                            addType(type, [Number(val1), Number(val2)]);
                        }
                        return false;
                    });
                    break;
                case 'equal':
                case 'notequal':
                    $('<span style="margin-left:10px;">给定比较值：</span>').appendTo(argsSpan);
                    var mt1 = $('<input  type="text" class="form-inp stext"/>').appendTo(argsSpan);
                    addBtn.on('click', function () {
                        var val1 = mt1.val();
                        addType(type, val1);
                        return false;
                    });
                    break;
                case 'equalto':
                    $('<span style="margin-left:10px;">控件ID：</span>').appendTo(argsSpan);
                    var mt1 = $('<input  type="text" class="form-inp integer"/>').appendTo(argsSpan);
                    addBtn.on('click', function () {
                        var val1 = mt1.val();
                        if (val1.length === 0) {
                            alert('必须填写控件ID！');
                            mt1.focus();
                            return false;
                        }
                        addType(type, '#' + val1);
                        return false;
                    });
                    break;
                case 'regex':
                    $('<span style="margin-left:10px;">请使用PHP和JS兼容的正则表达式：</span>').appendTo(argsSpan);
                    var mt1 = $('<input  type="text" class="form-inp stext"/>').appendTo(argsSpan);
                    addBtn.on('click', function () {
                        var val1 = mt1.val();
                        if (val1.length === 0) {
                            alert('必须填写正则表达式');
                            mt1.focus();
                            return false;
                        }
                        addType(type, val1);
                        return false;
                    });
                    break;
                case 'url':
                    $('<span style="margin-left:10px;">包括#号：</span>').appendTo(argsSpan);
                    var mt1 = $('<input type="checkbox">').appendTo(argsSpan);
                    addBtn.on('click', function () {
                        if (mt1.is(':checked')) {
                            addType(type, [true, true]);
                        } else {
                            addType(type, true);
                        }
                        return false;
                    });
                    break;
                default:
                    addBtn.on('click', function () {
                        addType(type, true);
                        return false;
                    });
                    break;
            }
        });
        updateDiv();
        that.on('blur', updateDiv);
        select.change();
    }

    function ToolValidMsg(element) {
        var that = $(element).hide();
        var valdBox = $('<div class="valdbox"></div>').insertAfter(that);
        var showBox = $('<div></div>').insertAfter(that);
        var select = $('<select class="form-inp select"><select>').appendTo(showBox);
        for (var key in config) {
            var it = config[key];
            var item = $('<option value="' + key + '">' + it.text + '|' + key + '</option>');
            select.append(item);
        }

        that.on('additem', function (ev, data) {
            select.val(data.type);
            addType(data.type, data.vals, false);
        });

        that.on('delitem', function (ev, type) {
            var items = valdBox.find('div.valid_item');
            items.each(function () {
                var itemd = $(this);
                var itdat = itemd.data('itdat');
                if (itdat[type]) {
                    itemd.remove();
                    update();
                    return false;
                }
            });
        });

        var argsSpan = $('<span></span>').appendTo(showBox);
        var addBtn = $('<a style="margin-left:10px;" class="form-inp button" href="#">添加</a>').appendTo(showBox);
        $('<a style="margin-left:10px;" class="form-inp button" href="#">显示</a>').appendTo(showBox).click(function () {
            if ($(this).text() === '显示') {
                that.show();
                $(this).text('隐藏');
                return false;
            } else {
                that.hide();
                $(this).text('显示');
                return false;
            }
        });
        var update = function () {
            var alldat = {};
            var items = valdBox.find('div.valid_item');
            if (items.length === 0) {
                that.val('');
            } else {
                items.each(function (index, element) {
                    var itdat = $(element).data('itdat');
                    alldat = $.extend(alldat, itdat);
                });
                that.data('alldat', alldat);
                $(that).triggerHandler('update');
                that.val(JSON.stringify(alldat));
            }
        };
        var updateDiv = function () {
            valdBox.empty();
            try {
                var boxval = that.val();
                var boxdata = JSON.parse(boxval);
                if (typeof (boxdata) === 'object') {
                    for (var key in boxdata) {
                        addType(key, boxdata[key], true);
                    }
                }
            } catch (ex) {

            }
        };
        var addType = function (type, vals, over) {
            if (over === false) {
                var items = valdBox.find('div.valid_item');
                var canadd = true;
                items.each(function () {
                    var itdat = $(this).data('itdat');
                    if (itdat[type]) {
                        canadd = false;
                        return false;
                    }
                });
                if (!canadd) {
                    return;
                }
            } else {
                var items = valdBox.find('div.valid_item');
                var canadd = true;
                items.each(function () {
                    var itemd = $(this);
                    var itdat = itemd.data('itdat');
                    if (itdat[type]) {
                        itdat[type] = vals;
                        itemd.data('itdat', itdat);
                        itemd.find('span').text(JSON.stringify(itdat));
                        canadd = false;
                        update();
                        return false;
                    }
                });
                if (!canadd) {
                    return;
                }

            }
            var itemd = $('<div style="line-height:20px;" class="valid_item"></div>').appendTo(valdBox);
            var itdat = {};
            itdat[type] = vals;
            $('<span></span>').text(JSON.stringify(itdat)).appendTo(itemd);
            $('<a style="margin-left:10px;" href="#">删除</a>').one('click', function () {
                $(this).parent().remove();
                update();
                return false;
            }).appendTo(itemd);
            itemd.data('itdat', itdat);
            update();
        };

        select.change(function () {
            var type = $(this).val();
            addBtn.unbind('click');
            argsSpan.empty();
            $('<span style="margin-left:10px;">出错提示消息：</span>').appendTo(argsSpan);
            var mt1 = $('<input  type="text" class="form-inp text"/>').appendTo(argsSpan);
            addBtn.on('click', function () {
                var val1 = mt1.val();
                if (val1.length === 0) {
                    return false;
                }
                addType(type, val1, true);
                return false;
            });
        });
        updateDiv();
        that.on('blur', updateDiv);
        select.change();
    }

    function ToolValidtorGroup(element) {

        var Timer = null;
        var that = $(element).hide();
        var showbox = $('<div></div>').insertAfter(that);
        var addbtn = $('<a style="margin-left:0px;" class="form-inp button" href="#">添加验证组</a>').appendTo(showbox);
        $('<a style="margin-left:10px;" class="form-inp button" href="#">显示</a>').appendTo(showbox).click(function () {
            if ($(this).text() === '显示') {
                that.show();
                $(this).text('隐藏');
                return false;
            } else {
                that.hide();
                $(this).text('显示');
                return false;
            }
        });

        var update = function () {
            var Bigalldat = {rule: [], message: []};
            var Bigitems = showbox.find('div.group-item');

            if (Bigitems.length === 0) {
                that.val('');
            } else {

                Bigitems.each(function () {
                    var itemboxs = $(this).find('div.group-item-boxlay textarea.itembox');
                    var itemmsgs = $(this).find('div.group-item-msglay textarea.itemmsg');
                    if (itemboxs.length === 0 || itemmsgs.length == 0) {
                        return;
                    } else {
                        var rule = itemboxs.data('alldat') || {};
                        var msgs = itemmsgs.data('alldat') || {};
                        Bigalldat.rule.push(rule);
                        Bigalldat.message.push(msgs);
                    }
                });

                that.val(JSON.stringify(Bigalldat));
            }
        };

        var addItem = function (rule, msg) {
            var groupItem = $('<div class="group-item" style="margin-top:5px; padding:5px; background-color:#fafeff; border:1px dashed #dfdfdf"></div>').appendTo(showbox);
            var boxLay = $('<div class="group-item-boxlay"></div>').appendTo(groupItem);
            var itemBox = $('<textarea class="form-inp textarea itembox" style="height:30px;" yee-module="valid-rule"/>').appendTo(boxLay);
            var msgLay = $('<div class="group-item-msglay"></div>').appendTo(groupItem);
            var itemMsg = $('<textarea class="form-inp textarea itemmsg" style="height:30px;"  yee-module="valid-message"/>').appendTo(msgLay);
            Yee.update(groupItem);
            if (rule) {
                itemBox.val(rule).triggerHandler('blur');
            }
            if (msg) {
                itemMsg.val(msg).triggerHandler('blur');
            }
            itemBox.on('additem', function (ev, data) {
                itemMsg.triggerHandler('additem', [data]);
            });
            itemBox.on('delitem', function (ev, data) {
                itemMsg.triggerHandler('delitem', [data]);
            });
            itemBox.on('update', function () {
                if (Timer !== null) {
                    window.clearTimeout(Timer);
                    Timer = null;
                }
                Timer = window.setTimeout(update, 200);
            });
            itemMsg.on('update', function () {
                if (Timer !== null) {
                    window.clearTimeout(Timer);
                    Timer = null;
                }
                Timer = window.setTimeout(update, 200);
            });
            $('<a style="margin-left:0px;" class="form-inp button" href="#">删除组</a>').one('click', function () {
                $(this).parent().remove();
                update();
                return false;
            }).appendTo(groupItem);
        };

        var updatediv = function () {
            showbox.find('div.group-item').remove();
            try {
                var boxval = that.val();
                var boxdata = JSON.parse(boxval);
                if (boxdata && $.isArray(boxdata.rule) && $.isArray(boxdata.msg)) {
                    for (var key = 0; key < boxdata.rule.length; key++) {
                        addItem(JSON.stringify(boxdata.rule[key]), JSON.stringify(boxdata.message[key] || null));
                    }
                }
            } catch (ex) {
            }
        };

        addbtn.on('click', function () {
            addItem(null, null);
            return false;
        });
        updatediv();
        that.on('blur', updatediv);

    }


    Yee.extend('valid-rule', 'textarea', ToolValidtor);
    Yee.extend('valid-message', 'textarea', ToolValidMsg);
    Yee.extend('valid-group', 'textarea', ToolValidtorGroup);

    $(function () {
        var validBox = $('#dataValRule');
        var msgBox = $('#dataValMessage');
        validBox.on('additem', function (ev, data) {
            msgBox.triggerHandler('additem', [data]);
        });
        validBox.on('delitem', function (ev, data) {
            msgBox.triggerHandler('delitem', [data]);
        });
    })

})(window.jQuery, window.Yee);


