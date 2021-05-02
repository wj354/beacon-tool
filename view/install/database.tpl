{extends file='install/layout.tpl'}
{block name='content'}
    <h2>数据库配置</h2>
    <div class="content">
        {foreach from=$form->getViewFields() item='field'}
            <div class="yee-row">
                <label class="row-label" style="width: 150px"><em></em>{$field->label}：</label>
                <div class="row-cell">
                    {input field=$field}
                    {if !empty($field->tips)}<p class="field-tips {$field->type}">{$field->tips|raw}</p>{/if}
                </div>
            </div>
        {/foreach}
        <div class="yee-row">
            <label class="row-label" style="width: 150px">提示：</label>
             <div class="row-cell">安装完成后管理员账号为 <span class="blue">admin</span> 密码为 <span class="blue">123456</span></div>
        </div>
    </div>
{/block}

{block name='btn'}
    <input type="submit" class="form-btn red" value="确定安装"/>
{/block}