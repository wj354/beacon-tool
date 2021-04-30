<div class="yee-row" id="row_{$field->boxId}">
    <label class="row-label">{if $field->star}<em></em>{/if}{$field->label}：</label>
    <div class="row-cell">
        {foreach from=$form->getViewFields() item='child'}
            {field_row field=$child}
        {/foreach}
    </div>
</div>
