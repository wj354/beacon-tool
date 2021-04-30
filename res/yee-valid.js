(function ($, Yee) {

    var config = {
        required: {
            text: '必填项', def: '{label}不可为空',
            other: {
                'RadioGroup': '请选择{label}',
                'Select': '请选择{label}',
                'DelaySelect': '请选择{label}',
                'CheckGroup': '请选择{label}',
                'UpFile': '请上传{label}',
                'UpImage': '请上传{label}'
            }
        },
        email: {text: '邮箱格式', def: '{label}格式不正确'},
        mobile: {text: '手机号码', def: '{label}格式不符'},
        idCard: {text: '身份证号码', def: '{label}格式不符'},
        number: {text: '数值形式', def: '{label}只能输入数值'},
        integer: {text: '整数形式', def: '{label}只能输入整数'},
        date: {text: '时间日期格式', def: '{label}格式错误'},
        min: {text: '最小范围', def: '{label}不能低于{0}'},
        max: {text: '最大范围', def: '{label}不能大于{0}'},
        range: {text: '区间范围', def: '{label}需在{0}-{1}之间'},
        minLength: {text: '最小长度', def: '{label}字数长度不足{0}位'},
        maxLength: {text: '最大长度', def: '{label}字数长度超出{0}位'},
        rangeLength: {text: '长度区间', def: '{label}字数长度需在{0}-{1}之间'},
        money: {text: '金额格式', def: '{label}格式不正确'},
        url: {text: 'URL格式', def: '{label}格式不正确'},
        equalTo: {text: '与控件值相等', def: '两次输入的{label}不一致'},
        regex: {text: '正则表达式', def: '{label}格式不符'},
    };

    function ToolValidate(element) {
        var that = $(element).hide();
        var validBox = $('<div class="valid-box"></div>').insertAfter(that);
        var showBox = $('<div></div>').insertAfter(that);
        //添加下拉框
        var select = $('<select class="form-inp select"><select>').appendTo(showBox);
        for (var key in config) {
            var it = config[key];
            var item = $('<option value="' + key + '">' + it.text + '|' + key + '</option>');
            select.append(item);
        }
        var msgSpan = $('<span style="margin-left:10px;"></span>').appendTo(showBox);
        var argsSpan = $('<span style="margin-left:10px;"></span>').appendTo(showBox);
        var msgBox = $('<input type="text" placeholder="请填写消息内容" class="form-inp text"/>').appendTo(msgSpan);
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
            var allData = {};
            var items = validBox.find('div.valid-item');
            if (items.length === 0) {
                that.val('');
            } else {
                items.each(function (index, element) {
                    var itData = $(element).data('itData');
                    allData = $.extend(allData, itData);
                });
                that.data('allData', allData);
                $(that).triggerHandler('update');
                that.val(JSON.stringify(allData));
            }
        };

        //更新图层
        var updateDiv = function () {
            validBox.empty();
            try {
                var boxVal = that.val();
                var boxData = JSON.parse(boxVal);
                if (typeof (boxData) === 'object') {
                    for (var key in boxData) {
                        addType(key, boxData[key]);
                    }
                }
            } catch (ex) {
            }
        };
        /**
         * 获取提示内容
         * @param type
         */
        var getMsg = function (type) {
            var msg = msgBox.val() || '';
            if (msg == '') {
                msg = getDefaultMsg(type);
            }
            return msg;
        };

        var getDefaultMsg = function (type) {
            var label = $('#label').val();
            var otType = $(':input[name=type]:checked').val();
            var msg = config[type] && config[type].def || '{label}格式不符';
            if (config[type] && config[type].other && config[type].other[otType]) {
                msg = config[type].other[otType];
            }
            return msg.replace('{label}', label);
        };

        //添加验证类型
        var addType = function (type, args) {
            var items = validBox.find('div.valid-item');
            var canAdd = true;
            items.each(function () {
                var item = $(this);
                var itData = item.data('itData');
                if (itData[type]) {
                    itData[type] = args;
                    item.data('itData', itData);
                    item.find('span').text(JSON.stringify(itData));
                    canAdd = false;
                    return false;
                }
            });
            if (!canAdd) {
                update();
                return;
            }
            var item = $('<div style="line-height:20px;" class="valid-item"></div>').appendTo(validBox);
            var itData = {};
            itData[type] = args;
            $('<span></span>').text(JSON.stringify(itData)).appendTo(item);
            $('<a style="margin-left:10px;" href="#">删除</a>').one('click', function () {
                $(this).parent().remove();
                update();
                return false;
            }).appendTo(item);
            item.data('itData', itData);
            update();
        };

        select.change(function () {
            var type = $(this).val();
            addBtn.off('click');
            argsSpan.empty();
            msgBox.val('');
            msgBox.attr('placeholder', getDefaultMsg(type));
            switch (type) {
                case 'min':
                    $('<span style="margin-left:10px;">最小：</span>').appendTo(argsSpan);
                    var mt1 = $('<input  type="text" style="width: 40px" class="form-inp integer"/>').appendTo(argsSpan);
                    $('<span style="margin-left:10px;">包括：</span>').appendTo(argsSpan);
                    var mt2 = $('<input type="checkbox">').appendTo(argsSpan);
                    addBtn.on('click', function () {
                        var val1 = mt1.val();
                        if (!/^[\-\+]?((\d+(\.\d*)?)|(\.\d+))$/.test(val1)) {
                            alert('最小范围必须是数字形式的数值！');
                            mt1.focus();
                            return false;
                        }
                        if (mt2.is(':checked')) {
                            addType(type, [Number(val1), true, getMsg(type)]);
                        } else {
                            addType(type, [Number(val1), getMsg(type)]);
                        }
                        return false;
                    });
                    break;
                case 'max':
                    $('<span style="margin-left:10px;">最大：</span>').appendTo(argsSpan);
                    var mt1 = $('<input  type="text" style="width: 40px" class="form-inp integer"/>').appendTo(argsSpan);
                    $('<span style="margin-left:10px;">包括：</span>').appendTo(argsSpan);
                    var mt2 = $('<input type="checkbox">').appendTo(argsSpan);
                    addBtn.on('click', function () {
                        var val1 = mt1.val();
                        if (!/^[\-\+]?((\d+(\.\d*)?)|(\.\d+))$/.test(val1)) {
                            alert('最大范围必须是数字形式的数值！');
                            mt1.focus();
                            return false;
                        }
                        if (mt2.is(':checked')) {
                            addType(type, [Number(val1), true, getMsg(type)]);
                        } else {
                            addType(type, [Number(val1), getMsg(type)]);
                        }
                        return false;
                    });
                    break;
                case 'minLength':
                    $('<span style="margin-left:10px;">最小：</span>').appendTo(argsSpan);
                    var mt1 = $('<input  type="text" style="width: 40px" class="form-inp integer"/>').appendTo(argsSpan);
                    $('<span style="margin-left:10px;">包括：</span>').appendTo(argsSpan);
                    var mt2 = $('<input type="checkbox">').appendTo(argsSpan);
                    addBtn.on('click', function () {
                        var val1 = mt1.val();
                        if (!/^[0-9]+$/.test(val1)) {
                            alert('最小长度必须是数字形式的数值！');
                            mt1.focus();
                            return false;
                        }
                        if (mt2.is(':checked')) {
                            addType(type, [parseInt(val1), true, getMsg(type)]);
                        } else {
                            addType(type, [parseInt(val1), getMsg(type)]);
                        }
                        return false;
                    });
                    break;
                case 'maxLength':
                    $('<span style="margin-left:10px;">最大：</span>').appendTo(argsSpan);
                    var mt1 = $('<input  type="text" style="width: 40px" class="form-inp integer"/>').appendTo(argsSpan);
                    $('<span style="margin-left:10px;">包括：</span>').appendTo(argsSpan);
                    var mt2 = $('<input type="checkbox">').appendTo(argsSpan);
                    addBtn.on('click', function () {
                        var val1 = mt1.val();
                        if (!/^[0-9]+$/.test(val1)) {
                            alert('最大长度必须是数字形式的数值！');
                            mt1.focus();
                            return false;
                        }
                        if (mt2.is(':checked')) {
                            addType(type, [parseInt(val1), true, getMsg(type)]);
                        } else {
                            addType(type, [parseInt(val1), getMsg(type)]);
                        }
                        return false;
                    });
                    break;
                case 'range':
                    $('<span style="margin-left:10px;">最小：</span>').appendTo(argsSpan);
                    var mt1 = $('<input  type="text" style="width: 40px" class="form-inp integer"/>').appendTo(argsSpan);
                    $('<span style="margin-left:10px;">最大：</span>').appendTo(argsSpan);
                    var mt2 = $('<input  type="text" style="width: 40px" class="form-inp integer"/>').appendTo(argsSpan);
                    $('<span style="margin-left:10px;">包括：</span>').appendTo(argsSpan);
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
                case 'rangeLength':
                    $('<span style="margin-left:10px;">最小：</span>').appendTo(argsSpan);
                    var mt1 = $('<input  type="text" style="width: 40px" class="form-inp integer"/>').appendTo(argsSpan);
                    $('<span style="margin-left:10px;">最大：</span>').appendTo(argsSpan);
                    var mt2 = $('<input  type="text" style="width: 40px" class="form-inp integer"/>').appendTo(argsSpan);
                    $('<span style="margin-left:10px;">包括：</span>').appendTo(argsSpan);
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
                            addType(type, [Number(val1), Number(val2), true, getMsg(type)]);
                        } else {
                            addType(type, [Number(val1), Number(val2), getMsg(type)]);
                        }
                        return false;
                    });
                    break;
                case 'equalTo':
                    $('<span style="margin-left:10px;">控件ID：</span>').appendTo(argsSpan);
                    var mt1 = $('<input  type="text" class="form-inp integer"/>').appendTo(argsSpan);
                    addBtn.on('click', function () {
                        var val1 = mt1.val();
                        if (val1.length === 0) {
                            alert('必须填写控件ID！');
                            mt1.focus();
                            return false;
                        }
                        addType(type, ['#' + val1, getMsg(type)]);
                        return false;
                    });
                    break;
                case 'regex':
                    $('<span style="margin-left:10px;">正则表达式：</span>').appendTo(argsSpan);
                    var mt1 = $('<input  type="text" class="form-inp stext"/>').appendTo(argsSpan);
                    addBtn.on('click', function () {
                        var val1 = mt1.val();
                        if (val1.length === 0) {
                            alert('必须填写正则表达式');
                            mt1.focus();
                            return false;
                        }
                        addType(type, [val1, getMsg(type)]);
                        return false;
                    });
                    break;
                case 'url':
                    $('<span style="margin-left:10px;">包括#号：</span>').appendTo(argsSpan);
                    var mt1 = $('<input type="checkbox">').appendTo(argsSpan);
                    addBtn.on('click', function () {
                        if (mt1.is(':checked')) {
                            addType(type, [true, getMsg(type)]);
                        } else {
                            addType(type, [getMsg(type)]);
                        }
                        return false;
                    });
                    break;
                default:
                    addBtn.on('click', function () {
                        addType(type, [getMsg(type)]);
                        return false;
                    });
                    break;
            }
        });
        updateDiv();
        that.on('blur', updateDiv);
        select.change();
    }

    function ToolValidGroup(element) {

        var Timer = null;
        var that = $(element).hide();
        var showBox = $('<div></div>').insertAfter(that);
        var addBtn = $('<a style="margin-left:0px;" class="form-inp button" href="#">添加验证组</a>').appendTo(showBox);
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
            var bigRule = [];
            var bigItems = showBox.find('div.group-item');

            if (bigItems.length === 0) {
                that.val('');
            } else {
                bigItems.each(function () {
                    var itemBox = $(this).find('div.group-item-boxlay textarea.itembox');
                    if (itemBox.length === 0) {
                        return;
                    } else {
                        var rule = itemBox.data('allData') || {};
                        bigRule.push(rule);
                    }
                });
                that.val(JSON.stringify(bigRule));
            }
        };

        var addItem = function (rule) {
            var groupItem = $('<div class="group-item" style="margin-top:5px; padding:5px; background-color:#fafeff; border:1px dashed #dfdfdf"></div>').appendTo(showBox);
            var boxLay = $('<div class="group-item-boxlay"></div>').appendTo(groupItem);
            var itemBox = $('<textarea class="form-inp textarea itembox" style="height:30px;" yee-module="valid-rule"/>').appendTo(boxLay);
            Yee.render(groupItem);
            if (rule) {
                itemBox.val(rule).triggerHandler('blur');
            }
            itemBox.on('update', function () {
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

        var updateDiv = function () {
            showBox.find('div.group-item').remove();
            try {
                var boxVal = that.val();
                var boxData = JSON.parse(boxVal);
                if (boxData && $.isArray(boxData)) {
                    for (var key = 0; key < boxData.length; key++) {
                        addItem(JSON.stringify(boxData[key]));
                    }
                }
            } catch (ex) {
            }
        };

        addBtn.on('click', function () {
            addItem(null);
            return false;
        });
        updateDiv();
        that.on('blur', updateDiv);
    }

    Yee.define('valid-rule', 'textarea', ToolValidate);
    Yee.define('valid-group', 'textarea', ToolValidGroup);

})(window.jQuery, window.Yee);


