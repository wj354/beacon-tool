{hook fn='multiple-wrap' field=null form=null lastIndex='0' body='' source=''}
<div class="yee-row" id="row_{$field->boxId}">
    <label class="row-label">{$field->label}：</label>
    <div class="row-cell">
    <div yee-module="container" id="{$field->boxId}" data-index="{$lastIndex}"{if $field->dataMinSize} data-min-size="{$field->dataMinSize}"{/if}{if $field->dataMaxSize} data-max-size="{$field->dataMaxSize}"{/if} data-source="{$source}">
        <div class="container-wrap" style="display: block">
            {$body|raw}
        </div>
        <div style="display: block;">
            <a href="{url ctl='Lists' act='operate' type='select'}" name="add-operate" yee-module="dialog" data-maxmin="false" data-width="550" data-height="500" class="yee-btn"><i class="icofont-plus-circle"></i>新增行</a>
            {if $field->tips}<span class="field-tips">{$field->tips}</span>{/if} <span id="{$field->boxId}-validation"></span>
        </div>
    </div>
    </div>
</div>
{/hook}
{hook fn='multiple-item' field=null form=null index='@@index@@'}
    <div class="container-item">
        <div class="yee-row">
            <table align="left" style="width:1020px" border="0">
                <tr>
                    <td rowspan="2" style="width: 750px;">{input field=$form->getField('code')}</td>
                    <td  valign="top">
                        <a href="javascript:;" name="remove" class="yee-btn"><i class="icofont-minus-circle"></i>移除</a>
                        <a href="javascript:;" name="insert" class="yee-btn"><i class="icofont-puzzle"></i>插入</a>
                        <a href="javascript:;" name="upsort" class="yee-btn"><i class="icofont-long-arrow-up"></i>上移</a>
                        <a href="javascript:;" name="dnsort" class="yee-btn"><i class="icofont-long-arrow-down"></i>下移</a>
                    </td>
                </tr>
            </table>
        </div>
    </div>
{/hook}
