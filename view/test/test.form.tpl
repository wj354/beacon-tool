{extends file='layout/form.tpl'}
{block name='title'}{$form->title}{/block}

{block name='form-header'}
    {if in_array(1,$formRow['viewBtns']) }
        <a class="yee-back" href="javascript:history.back();"><i class="icofont-reply"></i></a>
    {else}
        <a class="yee-setting" href="javascript:;"><i class="icofont-ruler-pencil"></i></a>
    {/if}
    <div class="yee-title">{$form->title}</div>
{/block}

{block name='form-information'}{if $formRow['information'] }
    <div class='yee-information'>{$formRow['information']}</div>{/if}{/block}
{block name='form-attention'}{if $formRow['attention'] }
    <div class='yee-attention'>{$formRow['attention']}</div>{/if}{/block}

{block name='form-content'}
    <form method="post" yee-module="validate ajax">
        {if $formRow['viewUseTab']}
            <div class="yee-tab">
                <ul yee-module="form-tab">
                    {foreach from=$formRow['viewTabs'] item=tab}
                        <li data-bind-name="{$tab.key}"{if $tab.first} class="curr"{/if}><a href="javascript:void(0);">{$tab.value}</a></li>
                    {/foreach}
                </ul>
            </div>
        {/if}

        <div class="yee-panel">

            {if $formRow['viewUseTab']}
                {foreach from=$formRow['viewTabs'] item=tab}
                    <div class="panel-content{if $tab.first==false} none{/if}" name="{$tab.key}">
                        <div class="panel-content">
                            {foreach from=$form->getViewFields($tab.key) item=field}
                                {field_row field=$field}
                            {/foreach}
                        </div>
                    </div>
                {/foreach}
            {else}
                <div class="panel-caption">
                    <i class="icofont-pencil-alt-3"></i>
                    <h3>{if $form->isAdd()}新增{else}编辑{/if}{$form->title}</h3>
                </div>
                <div class="panel-content">
                    {foreach from=$form->getViewFields() item=field}
                        {field_row field=$field}
                    {/foreach}
                </div>
            {/if}

            <div class="yee-submit">
                <label class="submit-label"></label>
                <div class="submit-cell">
                    {$form->fetchHideBox()|raw}
                    <input type="submit" class="form-btn red" value="提交">
                    {if in_array(1,$formRow['viewBtns']) }
                        <input type="hidden" name="__BACK__" value="{url act='index'}">
                        <a href="javascript:history.back();" class="form-btn back">返回</a>
                    {/if}
                    {if in_array(2,$formRow['viewBtns']) }
                        <a href="javascript:history.back();" onclick="Yee.closeDialog()" class="form-btn back">关闭</a>
                    {/if}
                    {if in_array(3,$formRow['viewBtns']) }
                        <input type="reset" class="form-btn" value="重置">
                    {/if}
                </div>
            </div>
        </div>
    </form>
{/block}