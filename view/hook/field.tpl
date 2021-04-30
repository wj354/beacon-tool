{hook fn='_choice' rs=null}<input type="checkbox" name="choice" value="{$rs.id}">{/hook}
{hook fn='id' rs=null}{$rs.id}{/hook}
{hook fn='label' rs=null}<input value="{$rs.label}" name="label" type="text" style="width: 100%; border: 0px; padding: 5px; margin: 0px;" yee-module="ajax"
                                data-url="{url act='editField' formId=$this->formId id=$rs.id}"/>{/hook}
{hook fn='name' rs=null}<input value="{$rs.name}" type="text" style="width: 1px; opacity: 0; border: 0px;"/>
    <a href="javascript:;" class="v-copy">
        <i class="icofont-copy"></i>
        <span style="font-size: 14px;{if !$rs.dbField}color: #999;{/if}{if $rs.close}color: #ccc;{/if}{if $rs.offEdit}text-decoration:underline;{/if}{if $rs.viewClose}font-style:italic;{/if}">{$rs.name}</span>
    </a>
{/hook}
{hook fn='type' rs=null}{$rs.type}{/hook}
{hook fn='dbtype' rs=null}{$rs.dbType}{/hook}
{hook fn='tabIndex' rs=null}{$rs.tabIndex}{/hook}
{hook fn='_close' rs=null}{if $rs.close}Close{/if}{/hook}
{hook fn='_viewClose' rs=null}{if $rs.viewClose}Hide{/if}{/hook}
{hook fn='sort' rs=null}{$rs.sort}{/hook}
{hook fn='_sort' rs=null}<input value="{$rs.sort}" name="sort" type="text" class="form-inp snumber tc" yee-module="integer ajax" data-url="{url act='sort' formId=$this->formId id=$rs.id}"/>{/hook}
{hook fn='_operate' rs=null}
    <a href="{url act='edit' formId=$this->formId id=$rs.id  appId=$this->appId}" class="yee-btn blue-bd"><i class="icofont-edit"></i>编辑</a>
    <a href="{url act='delete' formId=$this->formId id=$rs.id}" yee-module="confirm ajax" data-confirm@msg="确定要删除该字段了吗？" class="yee-btn red-bd reload"><i class="icofont-bin"></i>删除</a>
{/hook}
