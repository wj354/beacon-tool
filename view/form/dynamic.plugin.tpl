{*控件元素*}
{hook fn='item' field=null form=null index='@@index@@'}
    <!-- 键名 -->
    <div class="yee-row">
        <div class="row-cell">
            <span>{input field=$form->getField('name')}</span>
            <span>{input field=$form->getField('value')}</span>
            <a href="javascript:;" name="remove" class="yee-btn"><i class="icofont-minus-circle"></i>移除</a>
        </div>
    </div>
    <div class="yee-row">
        <div class="row-cell">
            {input field=$form->getField('d1')} {input field=$form->getField('r1')} <a href="{url ctl='AppField' act='table'}" class="yee-btn show-field">选择</a>
        </div>
    </div>
    <div class="yee-row">
        <div class="row-cell">
            {input field=$form->getField('d2')} {input field=$form->getField('r2')} <a href="{url ctl='AppField' act='table'}" class="yee-btn show-field">选择</a>
        </div>
    </div>
{/hook}

{*控件外包裹*}
{hook fn='wrap' field=null  body=''}
    <div class="yee-row" id="row_{$field->boxId}">
        <label class="row-label" style="line-height: 40px">{if $field->star}<em></em>{/if}{$field->label}：</label>
        <div class="row-cell">
            <div style="display: block;">{$body|raw}</div>
            <div style="display: block;">
                <a href="javascript:;" name="add" class="yee-btn"><i class="icofont-plus-circle"></i>新增行</a>
                <span id="{$field->boxId}-validation"></span>
            </div>
        </div>
    </div>
{/hook}