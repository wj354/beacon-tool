{*控件元素*}
{foreach from=$form->getViewFields() item=field}
    {field_row field=$field}
{/foreach}