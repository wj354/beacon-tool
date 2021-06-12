{*用于创建多行插件容器集合的hook函数模板 lastIndex 最后行的索引，body 已有item的模板渲染数据，source 用于js动态创建的模板数据base64  *}

{hook fn='wrap' field=null  body=null}
    <div class="yee-row" id="row_{$field->boxId}">
        <label class="row-label">{if $field->star}<em></em>{/if}{$field->label}：</label>
        <div class="row-cell">
            <div style="display: block;">{$body}</div>
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
        {foreach from=$form->getViewFields() item='child'}
            <div class="yee-row-inline" id="row_{$child->boxId}">
                <label class="inline-label">{$child->label}：</label>
                <span style="margin-right: 10px">{input field=$child}{if $child->tips}<span class="field-prompt">{$child->prompt}</span>{/if}</span>
            </div>
        {/foreach}
        {if !$field->offEdit}
            <div class="yee-row-inline" style="margin-right: 10px">
                {if $field->removeBtn}<a href="javascript:;" class="yee-btn" name="remove"><i class="icofont-minus-circle"></i>移除</a>{/if}
                {if $field->insertBtn}<a href="javascript:;" name="insert" class="yee-btn"><i class="icofont-puzzle"></i>插入</a>{/if}
                {if $field->sortBtn}<a href="javascript:;" name="upsort" class="yee-btn"><i class="icofont-long-arrow-up"></i>上移</a><a href="javascript:;" name="dnsort" class="yee-btn"><i class="icofont-long-arrow-down"></i>下移</a>{/if}
            </div>
        {/if}
    </div>
{/hook}