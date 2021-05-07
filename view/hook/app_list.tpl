{hook fn='id' rs=null}{$rs.id}{/hook}
{hook fn='key' rs=null}<input value="{$rs.key}" type="text" style="width: 1px; opacity: 0; border: 0px;"/>
    <a href="javascript:;" class="v-copy">
    <i class="icofont-copy"></i>
{$rs.key}</a>{/hook}
{hook fn='title' rs=null}{$rs.title}{/hook}
{hook fn='formKey' rs=null}{$rs.formKey}
    <a href="{url ctl='AppField' act='index' formId=$rs.formId}" yee-module="dialog"
       data-width="1280"
       data-height="800" class="blue">字段</a>
{/hook}

{hook fn='tbName' rs=null}<input value="@pf_{$rs.tbName}" type="text" style="width: 1px; opacity: 0; border: 0px;"/><a href="javascript:;" class="v-copy"><i class="icofont-copy"></i>@pf_{$rs.tbName}</a>{/hook}
{hook fn='_operate' rs=null}
    <a href="{url act='edit' id=$rs.id appId=$this->appId}" class="yee-btn blue-bd"><i class="icofont-edit"></i>编辑</a>
    <a href="{url ctl='AppSearch' listId=$rs.id appId=$this->appId}" class="yee-btn org2-bd"><i class="icofont-list"></i>搜索字段</a>
    <a href="{$rs.testUrl}" class="yee-btn green-bd" target="_blank"><i class="icofont-paint"></i>预览</a>
    <a href="{url act='add' copyId=$rs.id appId=$this->appId}" class="yee-btn"><i class="icofont-ui-add"></i>克隆</a>
    <a href="{url act='export' listId=$rs.id}" target="_blank" class="yee-btn">导出</a>
    <a href="{url act='del' id=$rs.id}" yee-module="confirm ajax" data-confirm@msg="确定要删除该项目了吗？" class="yee-btn red-bd reload"><i class="icofont-bin"></i>删除</a>
{/hook}
