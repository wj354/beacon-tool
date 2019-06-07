{extends file='layout/layoutForm.tpl'}

{block name='form-header'}
    <a class="yee-back" href="javascript:history.back();"><i class="icofont-reply"></i></a>
    <div class="yee-title">{$form->title}</div>
{/block}

{block name='form-content'}
    <form method="post" yee-module="validate ajax">
        <div class="yee-panel">
            <div class="panel-caption">
                <i class="icofont-pencil-alt-3"></i>
                <h3>{if $form->isAdd()}新增网站应用{else}编辑网站应用{/if}</h3>
            </div>
            <div class="panel-content">
                {foreach from=$form->getViewFields() item="field"}
                    <div class="yee-row">
                        <label class="row-label">{$field->label}：</label>
                        <div class="row-cell">
                            {input field=$field}
                            <span id="{$field->boxName}-validation"></span>
                            {if $field->tips}<p class="yee-field-tips">{$field->tips}</p>{/if}
                        </div>
                    </div>
                {/foreach}
            </div>
            <div class="yee-submit">
                <label class="submit-label"></label>
                <div class="submit-cell">
                    {$form->fetchHideBox()|raw}
                    <input type="submit" class="form-btn red" value="提交">
                    <input type="hidden" name="__BACK__" value="{url act='index'}">
                    <a href="javascript:history.back();" class="form-btn back">返回</a>
                </div>
            </div>
        </div>
    </form>
{/block}