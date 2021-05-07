{hook fn='item' field=null form=null index='@@index@@'}
    <div class="yee-row">
        <table align="left" style="width:1020px" border="0">
            <tr>
                <td rowspan="2" style="width: 750px;">{input field=$form->getField('code')}</td>
                <td valign="top">
                    <a href="javascript:;" name="remove" class="yee-btn"><i class="icofont-minus-circle"></i>移除</a>
                    <a href="{url ctl='AppList' act='operate' type='list'}" name="insert-operate" yee-module="dialog" data-maxmin="false" data-width="550" data-height="500" class="yee-btn"><i class="icofont-puzzle"></i>插入</a>
                    <a href="javascript:;" name="upsort" class="yee-btn"><i class="icofont-long-arrow-up"></i>上移</a>
                    <a href="javascript:;" name="dnsort" class="yee-btn"><i class="icofont-long-arrow-down"></i>下移</a>
                </td>
            </tr>
        </table>
    </div>
{/hook}

{*控件外包裹*}
{hook fn='wrap' field=null  body=''}
    <div class="yee-row" id="row_{$field->boxId}">
        <label class="row-label" style="line-height: 40px">{if $field->star}<em></em>{/if}{$field->label}：</label>
        <div class="row-cell">
            <div style="display: block;">{$body|raw}</div>
            <div style="display: block;">
                {*<a href="javascript:;" name="add" class="yee-btn"><i class="icofont-plus-circle"></i>新增行</a>*}
                <a href="{url ctl='AppList' act='operate' type='list'}" name="add-operate" yee-module="dialog" data-maxmin="false" data-width="550" data-height="500" class="yee-btn"><i class="icofont-plus-circle"></i>新增行</a>
                <span id="{$field->boxId}-validation"></span>
            </div>
        </div>
    </div>
{/hook}