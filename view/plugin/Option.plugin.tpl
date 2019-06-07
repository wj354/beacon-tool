{hook fn='multiple-wrap' field=null form=null lastIndex='0' body='' source=''}
    <div class="yee-row" id="row_{$field->boxId}">
        <label class="row-label">{$field->label}：</label>
        <div class="row-cell">
            <div class="option-container" yee-module="container" data-index="{$lastIndex}"{if $field->dataMinSize} data-min-size="{$field->dataMinSize}"{/if}{if $field->dataMaxSize} data-max-size="{$field->dataMaxSize}"{/if}
                 data-source="{$source}">
                <div class="container-wrap" style="display: block">
                    {$body|raw}
                </div>
                <div style="display: block;">
                    <a href="javascript:;" name="add" class="yee-btn"><i class="icofont-plus-circle"></i>新增行</a>
                    <span>
                        <input class="blue" style="border: none;width: 1px; opacity: 0" value="111"/>
                        <a href="javascript:;" class="yee-btn opt-copy"><i class="icofont-copy"></i>提取PHP数组</a>
                    </span>
                    {if $field->tips}<span class="field-tips">{$field->tips}</span>{/if} <span id="{$field->boxId}-validation"></span>
                </div>
            </div>
        </div>
    </div>
{/hook}
{hook fn='multiple-item' field=null form=null index='@@index@@'}
    <div class="container-item">
        {foreach from=$form->getViewFields() item=box}
            <div class="yee-row-inline" id="row_{$box->boxId}">
                <label class="inline-label">{$box->label}：</label>
                <span style="margin-right: 10px">
                    {input field=$box}
                </span>
            </div>
        {/foreach}
        <div class="yee-row-inline" style="margin-right: 10px">
            <a href="javascript:;" name="remove" class="yee-btn"><i class="icofont-minus-circle"></i>移除</a>
            <a href="javascript:;" name="insert" class="yee-btn"><i class="icofont-puzzle"></i>插入</a>
            <a href="javascript:;" name="upsort" class="yee-btn"><i class="icofont-long-arrow-up"></i>上移</a>
            <a href="javascript:;" name="dnsort" class="yee-btn"><i class="icofont-long-arrow-down"></i>下移</a>
        </div>
    </div>
{/hook}
