{hook fn='_choice' rs=null}<input type="checkbox" name="choice" value="{$rs.id}">{/hook}
{hook fn='id' rs=null}{$rs.id}{/hook}
{hook fn='label' rs=null}{$rs.label}{/hook}
{hook fn='name' rs=null}<input value="{$rs.name}" type="text" style="width: 1px; opacity: 0; border: 0px;"/><a href="javascript:;" class="v-copy"><i class="icofont-copy"></i>{$rs.name}</a>{/hook}
{hook fn='type' rs=null}{$rs.type}{/hook}
{hook fn='dbtype' rs=null}{$rs.dbType}{/hook}
{hook fn='tabIndex' rs=null}{$rs.tabIndex}{/hook}
{hook fn='sort' rs=null}{$rs.sort}{/hook}
