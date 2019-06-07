{extends file='layout/layoutForm.tpl'}
{block name='header'}
    <script src="{url ctl='r' act='index' file='lists-js'}"></script>
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
                <li data-bind-name="data"><a href="javascript:void(0);">数据查询</a></li>
                <li data-bind-name="operate"><a href="javascript:void(0);">操作项</a></li>
                <li data-bind-name="other"><a href="javascript:void(0);">其他</a></li>
            </ul>
        </div>
        <div class="yee-panel">
            <div class="panel-content" name="base">
                {foreach from=$form->getViewFields('base') item="field"}
                    {field_row field=$field}
                {/foreach}
            </div>

            <div class="panel-content" name="data" style="display: none">
                {foreach from=$form->getViewFields('data') item="field"}
                    {field_row field=$field}
                {/foreach}
            </div>

            <div class="panel-content" name="operate" style="display: none">
                {foreach from=$form->getViewFields('operate') item="field"}
                    {field_row field=$field}
                {/foreach}
            </div>

            <div class="panel-content" name="other" style="display: none">
                {foreach from=$form->getViewFields('other') item="field"}
                    {field_row field=$field}
                {/foreach}
            </div>

            <div class="yee-submit">
                <label class="submit-label"></label>
                <div class="submit-cell">
                    {$form->fetchHideBox()|raw}
                    {if $form->isAdd()}
                        <input type="submit" class="form-btn red" value="提交">
                    {/if}
                    {if $form->isEdit()}
                        <input type="submit" class="form-btn red" onclick="$(':input[name=\'__BACK__\']').val('{url act='index' appId=$this->get('appId')}')" value="提交">
                        <input type="submit" onclick="$(':input[name=\'__BACK__\']').val('')" class="form-btn org" value="仅保存">
                    {/if}
                    <input type="hidden" name="__BACK__" value="{url act='index' appId=$this->get('appId')}">
                    <a href="javascript:history.back();" class="form-btn back">返回</a>
                </div>
            </div>
        </div>


    </form>
{/block}