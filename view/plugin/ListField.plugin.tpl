{hook fn='multiple-wrap' field=null form=null lastIndex='0' body='' source=''}
    <div class="yee-row" id="row_{$field->boxId}">
        <label class="row-label">{$field->label}：</label>
        <div class="row-cell">
            <div yee-module="container" id="{$field->boxId}" data-index="{$lastIndex}"{if $field->dataMinSize} data-min-size="{$field->dataMinSize}"{/if}{if $field->dataMaxSize} data-max-size="{$field->dataMaxSize}"{/if} data-source="{$source}">
                <div class="container-wrap" style="display: block">
                    {$body|raw}
                </div>
                <div style="display: block;">
                    <a href="javascript:;" name="add" class="yee-btn"><i class="icofont-plus-circle"></i>新增行</a>
                    <label style="line-height: 20px;  height: 20px; display: inline-block; padding: 2px 7px; border: 0.5px solid #c1c1c1; border-radius: 4px; margin-left: 10px; color: #777; opacity: 0.9;">
                        <input id="select-all-btn" type="checkbox" class="selectCopy"> 全选
                    </label>
                    <a id="copy-btn" href="javascript:;" class="yee-btn"><i class="icofont-copy-alt"></i>拷贝</a>
                    <a id="paste-btn" href="javascript:;" class="yee-btn"><i class="icofont-copy-black"></i>黏贴</a>
                    <a id="delall-btn" href="javascript:;" class="yee-btn"><i class="icofont-copy-black"></i>删除选择</a>
                    <span id="{$field->boxId}-validation"></span>
                </div>
            </div>
        </div>
    </div>
{/hook}

{hook fn='multiple-item' field=null form=null index='@@index@@'}
    <div class="container-item">
        <div class="yee-container-title">
            <label class="inline-label" style="text-align: left;">&nbsp;&nbsp;
                <input type="checkbox" class="select-item">&nbsp;&nbsp;&nbsp;
                第 <span name="index" class="red2" style="font-size: 18px;"></span>项&nbsp;&nbsp;&nbsp;
            </label>
            <a href="javascript:;" name="remove" class="yee-btn"><i class="icofont-minus-circle"></i>移除</a>
            <a href="javascript:;" name="insert" class="yee-btn"><i class="icofont-puzzle"></i>插入</a>
            <a href="javascript:;" name="upsort" class="yee-btn"><i class="icofont-long-arrow-up"></i>上移</a>
            <a href="javascript:;" name="dnsort" class="yee-btn"><i class="icofont-long-arrow-down"></i>下移</a>
        </div>
        <div class="yee-container-body clearfix">
            <div style="float:left; width:550px">
                <div class="yee-row">
                    <label class="row-label" style="width: 60px">标题：</label>
                    <div class="row-cell">
                        {input field=$form->getField('title')} 排序：{input field=$form->getField('orderName')} <a class="yee-btn order-btn" href="javascript:;"><i class="icofont-swoosh-left"></i></a>
                    </div>
                </div>
                <div class="yee-row">
                    <label class="row-label" style="width: 60px">TH属性：</label>
                    <div class="row-cell">
                        {input field=$form->getField('thAlign')} {input field=$form->getField('thAttrs')} {input field=$form->getField('thWidth')}
                    </div>
                </div>
                <div class="yee-row">
                    <label class="row-label" style="width: 60px">TD属性：</label>
                    <div class="row-cell">
                        {input field=$form->getField('tdAlign')} {input field=$form->getField('tdAttrs')}
                    </div>
                </div>
            </div>
            <div style="float:left; width: 550px">
                <div class="yee-row">
                    <label class="row-label" style="width: 60px">字段：</label>
                    <div class="row-cell">
                        {input field=$form->getField('field') class="form-inp select dbfield"} 修饰键名：{input field=$form->getField('keyName')}
                    </div>
                </div>
                <div class="yee-row">
                    <label class="row-label" style="width: 60px">修饰值：</label>
                    <div class="row-cell">
                        {input field=$form->getField('code')}
                    </div>
                </div>
            </div>
        </div>
    </div>
{/hook}
