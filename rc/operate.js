$(function () {

    Yee.readyDialog(function (dialog) {
        $('#closeBtn').on('click', function () {
            dialog.close();
        });
        dialog.layer.iframeAuto(dialog.index);
        var textInp = $('#text');
        var urlInp = $('#url');
        var cssInp = $('#class');
        var iconInp = $('#icon');
        var styleInp = $('#style');
        var widthInp = $('#width');
        var heightInp = $('#height');
        var confirmInp = $('#confirm');
        var sValueInp = $('#s-value');
        var sTextInp = $('#s-text');
        var successInp = $('#success');
        var moduleInp = $(':input[name=module]');
        var changeModule = function () {
            var module = [];
            moduleInp.filter(':checked').each(function (_, el) {
                module.push($(el).val());
            });
            if (module.indexOf('ajax') > -1 || module.indexOf('dialog') > -1) {
                $('#row-success').show();
            } else {
                $('#row-success').hide();
            }
            if (module.indexOf('dialog') > -1) {
                $('#row-dialog').show();
            } else {
                $('#row-dialog').hide();
            }
            if (module.indexOf('confirm') > -1) {
                $('#row-confirm').show();
            } else {
                $('#row-confirm').hide();
            }
            if (module.indexOf('select-dialog') > -1) {
                $('#row-select').show();
            } else {
                $('#row-select').hide();
            }
            dialog.layer.iframeAuto(dialog.index);
            dialog.layer.iframeAuto(dialog.index);
        };
        moduleInp.on('click', changeModule);

        $('#submit').on('click', function () {
            var module = [];
            moduleInp.filter(':checked').each(function (_, el) {
                module.push($(el).val());
            });
            var text = textInp.val() || '';
            var url = urlInp.val() || '';
            var css = cssInp.val() || '';
            var icon = iconInp.val() || '';
            var style = styleInp.val() || '';
            var width = widthInp.val() || '';
            var height = heightInp.val() || '';
            var confirm = confirmInp.val() || '';
            var sValue = sValueInp.val() || '';
            var sText = sTextInp.val() || '';
            var success = successInp.val() || '';
            var out = [];
            out.push('<a');
            if (url) {
                out.push(' href="' + url + '"');
            }
            if (module.length > 0) {
                out.push(' yee-module="' + module.join(' ') + '"');
            }
            if (module.indexOf('dialog') > -1) {
                if (width) {
                    out.push(' data-width="' + width + '"');
                }
                if (height) {
                    out.push(' data-height="' + height + '"');
                }
            }
            if (module.indexOf('confirm') > -1) {
                if (confirm) {
                    out.push(' data-confirm@msg="' + confirm + '"');
                }
            }
            if (module.indexOf('select-dialog') > -1) {
                if (sValue) {
                    out.push(' data-value="' + sValue + '"');
                }
                if (sText) {
                    out.push(' data-text="' + sText + '"');
                }
            }
            if (module.indexOf('dialog') > -1 || module.indexOf('ajax') > -1) {
                if (success) {
                    out.push(' on-success="' + success + '"');
                }
            }
            if (css) {
                out.push(' class="' + css + '"');
            }
            if (style) {
                out.push(' style="' + style + '"');
            }
            out.push('>');
            if (icon) {
                out.push('<i class="' + icon + '"></i>');
            }
            if (text) {
                out.push(text);
            }
            out.push('</a>');
            var data = out.join('');
            dialog.success(data);
            dialog.close();
        });

        $('#quick').on('change', function () {
            var val = $(this).val();
            moduleInp.prop('checked', false);
            switch (val) {
                case 'add':
                    textInp.val('新增');
                    urlInp.val("{url act='add'}");
                    cssInp.val("yee-btn red");
                    iconInp.val("icofont-patient-file");
                    break;
                case 'add2':
                    textInp.val('新增子项');
                    urlInp.val("{url act='add' pid=$rs.id}");
                    cssInp.val("yee-btn red");
                    iconInp.val("icofont-patient-file");
                    break;
                case 'edit':
                    textInp.val('编辑');
                    urlInp.val("{url act='edit' id=$rs.id}");
                    cssInp.val("yee-btn blue-bd");
                    iconInp.val("icofont-pencil-alt-5");
                    break;
                case 'choice':
                    textInp.val('选择');
                    urlInp.val("{url act='choice' id=$rs.id}");
                    cssInp.val("yee-btn");
                    iconInp.val("icofont-check-circled");
                    moduleInp.eq(4).prop('checked', true);
                    sValueInp.val('{$rs.id}');
                    sTextInp.val('{$rs.name}');
                    break;
                case 'allow':
                    //<a href="{url act='toggleAllow' id=$rs.id}" yee-module="ajax"  class="yee-btn">{if $rs.allow}<i class="icofont-not-allowed"></i>禁用{else}<i class="icofont-verification-check"></i>审核{/if}</a>
                    textInp.val('{if $rs.allow}<i class="icofont-not-allowed"></i>禁用{else}<i class="icofont-verification-check"></i>审核{/if}');
                    urlInp.val("{url act='toggleAllow' id=$rs.id}");
                    cssInp.val("yee-btn");
                    moduleInp.eq(0).prop('checked', true);
                    iconInp.val("");
                    successInp.val("$('#list').emit('reload');");
                    break;
                case 'delete':
                    //<a href="{url act='delete' id=$rs.id}" yee-module="confirm ajax" data-confirm@msg="确定要删除该数据了吗？" class="yee-btn red-bd reload"><i class="icofont-bin"></i>删除</a>
                    textInp.val('删除');
                    urlInp.val("{url act='delete' id=$rs.id}");
                    cssInp.val("yee-btn red-bd");
                    moduleInp.eq(0).prop('checked', true);
                    moduleInp.eq(2).prop('checked', true);
                    iconInp.val("icofont-pencil-alt-5");
                    confirmInp.val('确定要删除该数据了吗？');
                    successInp.val("$('#list').emit('reload');");
                    break;
                case 'allowChoice':
                    //<a href="{url act='allowChoice'}" yee-module="ajax choice" class="yee-btn red-bd reload"><i class="icofont-verification-check"></i>审核所选</a>
                    textInp.val('审核所选');
                    urlInp.val("{url act='allowChoice'}");
                    cssInp.val("yee-btn");
                    moduleInp.eq(0).prop('checked', true);
                    moduleInp.eq(3).prop('checked', true);
                    iconInp.val("icofont-verification-check");
                    successInp.val("$('#list').emit('reload');");
                    break;
                case 'revokeChoice':
                    //<a href="{url act='revokeChoice'}" yee-module="ajax choice" class="yee-btn red-bd reload"><i class="icofont-not-allowed"></i>禁用所选</a>
                    textInp.val('禁用所选');
                    urlInp.val("{url act='revokeChoice'}");
                    cssInp.val("yee-btn");
                    moduleInp.eq(0).prop('checked', true);
                    moduleInp.eq(3).prop('checked', true);
                    iconInp.val("icofont-not-allowed");
                    successInp.val("$('#list').emit('reload');");
                    break;
                case 'deleteChoice':
                    //<a href="{url act='revokeChoice'}" yee-module="ajax choice" class="yee-btn red-bd reload"><i class="icofont-not-allowed"></i>禁用所选</a>
                    textInp.val('删除所选');
                    urlInp.val("{url act='deleteChoice'}");
                    cssInp.val("yee-btn red-bd");
                    moduleInp.eq(0).prop('checked', true);
                    moduleInp.eq(3).prop('checked', true);
                    moduleInp.eq(2).prop('checked', true);
                    iconInp.val("icofont-pencil-alt-5");
                    confirmInp.val('确定要删除所选数据了吗？');
                    successInp.val("$('#list').emit('reload');");
                    break;
                default:
                    break;
            }
            changeModule();
        });

    });
});