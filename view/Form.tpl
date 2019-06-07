{extends file='layout/layoutList.tpl'}
{block name="title"}表单管理{/block}
{block name="caption"}工具-表单管理{/block}

{block name='list-header'}
    <div class="yee-list-header">
        <div class="yee-caption"><i class="icofont-listine-dots"></i> 工具-表单管理</div>
        <div class="yee-toolbar">
            <span> 共 <span id="records-count">0</span> 条记录</span>
            <a href="javascript:window.location.reload()" class="refresh-btn"><i class="icofont-refresh"></i>刷新</a>
            <a id="add-btn" href="{url act='add' appId=$appId}" class="yee-btn red"><i class="icofont-patient-file"></i>添加表单</a>
        </div>
    </div>
{/block}

{block name='list-search'}
    <div class="yee-list-search">
        <form id="searchForm" yee-module="search-form" data-bind="#list">
            <div class="yee-cell">
                <label class="yee-section-label"><em></em>表单名称：</label>
                <span><input name="name" class="form-inp text" type="text"/></span>
            </div>
            <div class="yee-cell">
                <input class="form-btn blue" value="查询" type="submit"/>
                <input class="form-btn normal" value="重置" type="reset"/>
                <input type="hidden" name="appId" value="{$appId}">
                <a class="senior-btn" onclick="$('.yee-search').toggleClass('senior')">高级搜索<i></i></a>
            </div>
            {literal left='<{' right='}>'}
                <div class="yee-cell fr">
                    <a href="<{url act='import' appId=$appId}>"
                       yee-module="dialog"
                       data-width="550"
                       data-height="300"
                       data-maxmin="false"
                       data-auto-size="true"
                       class="yee-btn" on-success="window.location.href=Yee.url('<{url act='addImport' appId=$appId}>',{import:ret});"><i class="icofont-copy"></i>导入表单</a>
                </div>
            {/literal}
        </form>
    </div>
{/block}

{block name='list-tab'}
    <div class="yee-tab">
        <ul yee-module="list-tab">
            <li{if $appId==0} class="curr"{/if}><a href="{url ctl='Form' act='index' appId=0}" data-tab-index='0'>全部</a>
            </li>
            {foreach from=$applist item="rs"}
                <li{if $appId==$rs.id} class="curr"{/if}><a href="{url ctl='Form' act='index' appId=$rs.id}"
                                                            data-tab-index='{$rs.id}'>{$rs.name}</a></li>
            {/foreach}
        </ul>
    </div>
{/block}

{block name='list-table'}
    <table id="list" class="yee-datatable" yee-module="datatable"
           data-auto-load="true"
           width="100%">
        <thead>
        <tr>
            <th width="40" data-order="id">ID</th>
            <th width="160" align="left" data-order="key">标识名称</th>
            <th>表单名称</th>
            <th width="120">类型</th>
            <th width="200">数据表名称</th>
            <th width="150">应用名称</th>
            <th width="420" align="right">操作</th>
        </tr>
        </thead>
        <tbody yee-template>
        <tr yee-each="list" yee-item="rs">
            <td align="center" :html="rs.id"></td>
            <td align="left" :html="rs.key"></td>
            <td align="left" :html="rs.title"></td>
            <td align="center" :html="rs.extMode"></td>
            <td align="center" :html="rs.tbName"></td>
            <td align="center" :html="rs.appName"></td>
            <td align="right" :html="rs._operate"></td>
        </tr>
        <tr yee-if="list.length==0">
            <td colspan="100">没有任何数据！</td>
        </tr>
        </tbody>
    </table>
{/block}

{block name='list-pagebar'}
    <div yee-module="pagebar" data-bind="#list" class="yee-pagebar">
        <div yee-template class="pagebar" :html="barCode"></div>
        <div yee-template class="pagebar-info">
            共有信息：<span :text="recordsCount"></span> 页次：<span :text="page"></span>/<span :text="pageCount"></span> 每页
            <span :text="pageSize"></span>
        </div>
    </div>
{/block}
