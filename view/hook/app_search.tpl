{hook fn='_choice' rs=null}<input type="checkbox" name="choice" value="{$rs.id}">{/hook}
{hook fn='id' rs=null}{$rs.id}{/hook}
{hook fn='label' rs=null}{$rs.label}{/hook}
{hook fn='name' rs=null}<input value="{$rs.name}" type="text" style="width: 1px; opacity: 0; border: 0px;"/><a href="javascript:;" class="v-copy"><i class="icofont-copy"></i>{$rs.name}</a>{/hook}
{hook fn='type' rs=null}{$rs.type}{/hook}
{hook fn='tabIndex' rs=null}{$rs.tabIndex}{/hook}
{hook fn='sort' rs=null}{$rs.sort}{/hook}
{hook fn='_sort' rs=null}<input value="{$rs.sort}" name="sort" type="text" class="form-inp snumber" yee-module="integer ajax" data-url="{url act='sort' listId=$this->listId id=$rs.id}"/>{/hook}
{hook fn='_operate' rs=null}
    <a href="{url act='edit' listId=$this->listId id=$rs.id}" class="yee-btn blue-bd"><i class="icofont-edit"></i>编辑</a>
    <a href="{url act='delete' listId=$this->listId id=$rs.id}" yee-module="confirm ajax" data-confirm@msg="确定要删除该字段了吗？" class="yee-btn red-bd reload"><i class="icofont-bin"></i>删除</a>
{/hook}

