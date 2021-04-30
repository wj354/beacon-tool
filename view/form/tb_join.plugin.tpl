{*控件元素*}
{hook fn='item' field=null form=null index='@@index@@'}
    <!-- 键名 -->
    <div class="row-cell">
        <label class="inline-label" style="color: #888"><em></em>附加表：</label><span>{input field=$form->getField('name')}</span>
        <label class="inline-label" style="color: #888;margin-left: 5px;"><em></em>JOIN：</label><span>{input field=$form->getField('join')}</span>
        <label class="inline-label" style="color: #888;margin-left: 5px;"><em></em>ON：</label><span>{input field=$form->getField('on')}</span>
        <div class="yee-row-inline" style="margin-right: 10px">
            <a href="javascript:;" name="remove" class="yee-btn"><i class="icofont-minus-circle"></i>移除</a>
            <a href="javascript:;" name="insert" class="yee-btn"><i class="icofont-puzzle"></i>插入</a>
            <a href="javascript:;" name="upsort" class="yee-btn"><i class="icofont-long-arrow-up"></i>上移</a>
            <a href="javascript:;" name="dnsort" class="yee-btn"><i class="icofont-long-arrow-down"></i>下移</a>
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