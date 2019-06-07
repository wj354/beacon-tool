{hook fn='single' field=null form=null}
    <div class="yee-line"><label class="line-label">默认值设置</label></div>
{foreach from=$form->getViewFields() item="field"}
    {field_row field=$field}
{/foreach}
{/hook}
