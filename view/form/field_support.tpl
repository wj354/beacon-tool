<div class="yee-line"><label class="line-label">控件专用属性设置</label></div>
{foreach from=$form->getViewFields() item="field"}
    {field_row field=$field}
{/foreach}