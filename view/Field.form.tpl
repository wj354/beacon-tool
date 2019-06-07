{extends file='layout/layoutForm.tpl'}

{block name='header'}
    <script src="{url ctl='r' act='index' file='yee-valid-js'}"></script>
    <script src="{url ctl='r' act='index' file='field-type-js'}"></script>
{/block}

{block name='form-header'}
    <a class="yee-back" href="javascript:history.back();"><i class="icofont-reply"></i></a>
    <div class="yee-title">{$form->title}</div>
{/block}

{block name='form-content'}
    <form method="post" yee-module="validate ajax">
        <div class="yee-tab">
            <ul yee-module="form-tab">
                <li data-bind-name="base" class="curr"><a href="javascript:void(0);">基本配置</a></li>
                <li data-bind-name="extend"><a href="javascript:void(0);">高级配置</a></li>
                <li data-bind-name="view"><a href="javascript:void(0);">视图及属性</a></li>
                <li data-bind-name="valid"><a href="javascript:void(0);">验证相关</a></li>
            </ul>
        </div>
        <div class="yee-panel">
            <div class="panel-content" name="base">
                {foreach from=$form->getViewFields('base') item="field"}
                    {field_row field=$field}
                {/foreach}
            </div>

            <div class="panel-content" name="extend" style="display: none">
                {foreach from=$form->getViewFields('extend') item="field"}
                    {field_row field=$field}
                {/foreach}
            </div>

            <div class="panel-content" name="view" style="display: none">
                {foreach from=$form->getViewFields('view') item="field"}
                    {field_row field=$field}
                {/foreach}
            </div>

            <div class="panel-content" name="valid" style="display: none">
                {foreach from=$form->getViewFields('valid') item="field"}
                    {field_row field=$field}
                {/foreach}
            </div>

            <div class="yee-submit">
                <label class="submit-label"></label>
                <div class="submit-cell">
                    {$form->fetchHideBox()|raw}
                    <input type="submit" class="form-btn red" value="提交">
                    {if $form->isAdd()}{literal}
                        <input type="submit" onclick="$(':input[name=\'__BACK__\']').val(window.location.href)" class="form-btn org" value="继续添加">
                    {/literal}{/if}
                    <input type="hidden" name="__BACK__" value="{url act='index' formId=$this->formId appId=$this->get('appId:s', '')}">
                    <a href="javascript:history.back();" class="form-btn back">返回</a>
                </div>
            </div>
        </div>


    </form>
{/block}