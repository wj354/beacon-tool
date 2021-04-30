{hook fn='id' rs=null}{$rs.id}{/hook}
{hook fn='name' rs=null}{$rs.name}{/hook}
{hook fn='namespace' rs=null}{$rs.namespace}{/hook}
{hook fn='isDefault' rs=null}{if $rs.isDefault}<span class="green">是</span>{else}<span class="gray">否</span>{/if}{/hook}
{hook fn='_operate' rs=null}
    <a href="{url ctl="AppForm" act='index' appId=$rs.id}" class="yee-btn">表单管理</a>
    <a href="{url ctl="AppList" act='index' appId=$rs.id}" class="yee-btn">列表管理</a>
    <a href="{url act='make' id=$rs.id}" yee-module="confirm ajax" data-confirm@msg="确定要重新生成代码了吗？" on-success="$('#list').emit('reload');" class="yee-btn org2-bd"><i class="icofont-paper-plane"></i>重新生成</a>
    <a href="{url act='edit' id=$rs.id}" on-success="$('#list').emit('reload');" class="yee-btn blue-bd"><i class="icofont-pencil-alt-5"></i>编辑</a>
    <a href="{url act='delete' id=$rs.id}" yee-module="confirm ajax" data-confirm@msg="确定要删除该项目了吗？" class="yee-btn red-bd reload"><i class="icofont-bin"></i>删除</a>
{/hook}
{hook fn='_select' rs=null}<a class="yee-btn" href="javascript:;" yee-module="select-dialog" data-value="{$rs.id}" data-text="{$rs.name} ({$rs.namespace})">选择该项</a>{/hook}