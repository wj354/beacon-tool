{extends file='layout/form.tpl'}
{block name='title'}{$form->title}{/block}

{block name='header'}
    <script src="{url ctl='Res' f="app-list.js"}"></script>
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
                {foreach from=$form->getViewFields('base') item=field}
                    {field_row field=$field}
                {/foreach}
            </div>

            <div class="panel-content" name="data">
                {foreach from=$form->getViewFields('data') item=field}
                    {field_row field=$field}
                {/foreach}
            </div>

            <div class="panel-content" name="operate">
                {foreach from=$form->getViewFields('operate') item=field}
                    {field_row field=$field}
                {/foreach}
            </div>

            <div class="panel-content" name="other">
                {foreach from=$form->getViewFields('other') item=field}
                    {field_row field=$field}
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