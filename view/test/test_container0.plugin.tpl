{*用于创建多行插件容器集合的hook函数模板 lastIndex 最后行的索引，body 已有item的模板渲染数据，source 用于js动态创建的模板数据base64  *}

{hook fn='wrap' field=null  body=null}
    <div class="yee-row" id="row_{$field->boxId}">
        <label class="row-label">{if $field->star}<em></em>{/if}{$field->label}：</label>
        <div class="row-cell">
            <div style="display: block;">{$body|raw}</div>
            <div style="display: block;">
                {if !$field->offEdit}<a href="javascript:;" name="add" class="yee-btn"><i class="icofont-plus-circle"></i>新增行</a>{/if}
                {if $field->prompt}<span class="yee-field-prompt">{$field->prompt}</span>{/if} <span id="{$field->boxId}-validation"></span>
            </div>
        </div>
    </div>
{/hook}

{*用于创建多行插件容器中每行的数据hook函数模板 form 插件的表单 index 每项的索引*}
{hook fn='item' field=null form=null index='@@index@@'}
    <div class="container-item">

        <div class="yee-container-title">
            <label class="inline-label" style="text-align: left;">&nbsp;&nbsp; 第 <span name="index" class="red2" style="font-size: 18px;"></span>项&nbsp;&nbsp;&nbsp;</label>
            {if !$field->offEdit}
                {if $field->removeBtn}<a href="javascript:;" class="yee-btn" name="remove"><i class="icofont-minus-circle"></i>移除</a>{/if}
                {if $field->insertBtn}<a href="javascript:;" name="insert" class="yee-btn"><i class="icofont-puzzle"></i>插入</a>{/if}
                {if $field->sortBtn}<a href="javascript:;" name="upsort" class="yee-btn"><i class="icofont-long-arrow-up"></i>上移</a><a href="javascript:;" name="dnsort" class="yee-btn"><i class="icofont-long-arrow-down"></i>下移</a>{/if}
            {/if}
        </div>
        <div class="yee-container-body">
            {foreach from=$form->getViewFields() item='child'}
                {field_row field=$child}
            {/foreach}
        </div>
    </div>
{/hook}