{*控件元素*}
{hook fn='item' field=null form=null index='@@index@@'}
    <!-- 键名 -->
    <div class="row-cell" id="row_{$form->getField('key')->boxId}">
        <label class="inline-label" style="color: #888"><em></em>键名：</label><span>{input field=$form->getField('key')}</span>
        <label class="inline-label" style="color: #888;margin-left: 5px;"><em></em>值：</label><span>{input field=$form->getField('value')}</span>
        <a href="javascript:;" name="remove" class="yee-btn"><i class="icofont-bin"></i>删除</a>
    </div>
{/hook}

{*控件外包裹*}
{hook fn='wrap' field=null  body=''}
    <div class="yee-row" id="row_{$field->boxId}">
        <label class="row-label" style="line-height: 40px">{if $field->star}<em></em>{/if}{$field->label}：</label>
        <div class="row-cell">
            <div style="display: block;">{$body}</div>
            <div style="display: block;">
                <a href="javascript:;" name="add" class="yee-btn"><i class="icofont-plus-circle"></i>新增行</a>
                <span id="{$field->boxId}-validation"></span>
            </div>
        </div>
    </div>
{/hook}