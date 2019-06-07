{hook fn='multiple-wrap' field=null form=null lastIndex='0' body='' source=''}
    <div class="yee-row" id="row_{$field->boxId}">
        <label class="row-label">{$field->label}：</label>
        <div class="row-cell">
            <div id="{$field->boxId}" yee-module="container" data-index="{$lastIndex}"{if $field->dataMinSize} data-min-size="{$field->dataMinSize}"{/if}{if $field->dataMaxSize} data-max-size="{$field->dataMaxSize}"{/if} data-source="{$source}">
                <div class="container-wrap" style="display: block">
                    {$body|raw}
                </div>
                <div style="display: block;">
                    <a href="javascript:;" name="add" class="yee-btn"><i class="icofont-plus-circle"></i>新增行</a>
                    {if $field->tips}<span class="field-tips">{$field->tips}</span>{/if} <span id="{$field->boxId}-validation"></span>
                </div>
            </div>
        </div>
    </div>
{/hook}
{hook fn='multiple-item' field=null form=null index='@@index@@'}
    <div class="container-item">
        <div class="yee-row">
            <div class="row-cell">
                {input field=$form->getField('name')} {input field=$form->getField('value')} <a href="javascript:;" name="remove" class="yee-btn"><i class="icofont-minus-circle"></i>移除</a>
            </div>
        </div>
        <div class="yee-row">
            <div class="row-cell">
                {input field=$form->getField('d1')} {input field=$form->getField('r1')} <a href="{url ctl='Field' act='mdfield'}" class="yee-btn show-field">选择</a>
            </div>
        </div>
        <div class="yee-row">
            <div class="row-cell">
                {input field=$form->getField('d2')} {input field=$form->getField('r2')} <a href="{url ctl='Field' act='mdfield'}" class="yee-btn show-field">选择</a>
            </div>
        </div>
    </div>
{/hook}
