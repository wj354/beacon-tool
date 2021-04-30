{hook fn='id' rs=null}{$rs.id}{/hook}
{hook fn='key' rs=null}{$rs.key}{/hook}
{hook fn='title' rs=null}{$rs.title}{/hook}
{hook fn='appName' rs=null}{$rs.appName}{/hook}
{hook fn='extMode' rs=null}{$rs.extMode|match:[0=>'普通模式',1=>'<span class="blue">插件模式</span>',2=>'<span class="org">分类层级</span>',3=>'普通模式',4=>'<span class="green">继承表</span>']|raw}{/hook}
{hook fn='tbName' rs=null}{if $rs.extMode==1}--{else}<input value="@pf_{$rs.tbName}" type="text" style="width: 1px; opacity: 0; border: 0px;"/><a href="javascript:;" class="v-copy"><i class="icofont-copy"></i>@pf_{$rs.tbName}</a>{/if}{/hook}
{hook fn='_operate' rs=null}
    <a href="{url act='edit' id=$rs.id appId=$this->appId}" class="yee-btn blue-bd"><i class="icofont-edit"></i>编辑</a>
    <a href="{url ctl='app_field' formId=$rs.id appId=$this->appId}" class="yee-btn blue"><i class="icofont-list"></i>字段管理</a>
    <a href="{url ctl='app_test' act='form' appId=$this->appId formId=$rs.id}" class="yee-btn green-bd" target="_blank"><i class="icofont-paint"></i>测试</a>
    <a href="{url act='add' copyId=$rs.id appId=$this->appId}" class="yee-btn"><i class="icofont-ui-add"></i>克隆</a>
    <a href="{url act='export' formId=$rs.id}" target="_blank" class="yee-btn">导出</a>
    <a href="{url act='del' id=$rs.id}" yee-module="confirm ajax" data-confirm@msg="确定要删除该项目了吗？" class="yee-btn red-bd reload"><i class="icofont-bin"></i>删除</a>
{/hook}