{*控件元素*}
{hook fn='item' field=null form=null index='@@index@@'}
    <div class="yee-row-inline clearfix" style="background:#f8f8f8; display: block; line-height: 24px;">
        <label class="inline-label" style="text-align: left;">&nbsp;&nbsp;
            第 <span name="index" class="red2" style="font-size: 18px;"></span>项&nbsp;&nbsp;&nbsp;
        </label>
        <a href="javascript:;" name="remove" class="yee-btn"><i class="icofont-minus-circle"></i>移除</a>
        <a href="javascript:;" name="insert" class="yee-btn"><i class="icofont-puzzle"></i>插入</a>
        <a href="javascript:;" name="upsort" class="yee-btn"><i class="icofont-long-arrow-up"></i>上移</a>
        <a href="javascript:;" name="dnsort" class="yee-btn"><i class="icofont-long-arrow-down"></i>下移</a>
    </div>
    <div style="border: 1px #eee dotted; padding:5px 8px">
        <div class="yee-row-inline" style="display: block">
            <label class="inline-label" style="width: 60px">名称：</label>
            <div class="inline-cell">
                {input field=$form->getField('name')} URL：{input field=$form->getField('url')} 是否使用代码：{input field=$form->getField('useCode')}
            </div>
        </div>
        <div class="yee-row-inline" id="row_{$form->getField('code')->boxId}" style="display: block">
            <label class="inline-label" style="width: 60px">代码：</label>
            <div class="inline-cell">
                {input field=$form->getField('code')}
            </div>
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