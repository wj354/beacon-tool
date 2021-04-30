{extends file='layout/list.tpl'}
{block name="title"}项目管理{/block}
{block name="caption"}工具-项目管理{/block}

{block name='list-header'}
    <div class="yee-list-header">
        <div class="yee-caption"><i class="icofont-users-alt-2"></i> 工具-项目管理</div>
        <div class="yee-toolbar">
            <span> 共 <span id="records-count">0</span> 条记录</span>
            <a href="javascript:window.location.reload()" class="refresh-btn"><i class="icofont-refresh"></i>刷新</a>
            <a id="add-btn" href="{url act='add'}" class="yee-btn red"><i class="icofont-patient-file"></i>添加项目</a>
        </div>
    </div>
{/block}

{block name='list-search'}
    <div class="yee-list-search">
        <form id="searchForm" yee-module="search-form" data-bind="#list">
            <div class="yee-cell">
                <label class="yee-section-label"><em></em>项目名称：</label>
                <span><input name="name" class="form-inp text" type="text"/></span>
            </div>
            <div class="yee-cell">
                <input class="form-btn blue" value="查询" type="submit"/>
                <input class="form-btn normal" value="重置" type="reset"/>
                <a class="senior-btn" onclick="$('.yee-search').toggleClass('senior')">高级搜索<i></i></a>
            </div>
        </form>
    </div>
{/block}

{block name='list-table'}
    <table id="list" class="yee-datatable" yee-module="datatable" data-auto-load="true" width="100%">
        <thead>
        <tr>
            <th width="40" data-order="id">ID</th>
            <th align="left" data-order="name">项目名称</th>
            <th width="200" align="left">命名空间</th>
            <th width="80" align="left">默认项目</th>
            <th width="100" data-fixed="right">操作</th>
        </tr>
        </thead>
        <tbody yee-template>
        <tr v-for="rs in list">
            <td v-html="rs.id"></td>
            <td v-html="rs.name"></td>
            <td v-html="rs.namespace"></td>
            <td align="center" v-html="rs.isDefault"></td>
            <td class="opt-btns" v-html="rs._select"></td>
        </tr>
        <tr v-if="list.length==0">
            <td colspan="100">没有任何数据！</td>
        </tr>
        </tbody>
    </table>
{/block}

{block name='list-pagebar'}
    <div yee-module="pagebar" data-bind="#list" class="yee-pagebar">
        <div yee-template class="pagebar" v-html="barCode"></div>
        <div yee-template class="pagebar-info">
            共有信息：<span v-text="recordsCount"></span> 页次：<span v-text="page"></span>/<span v-text="pageCount"></span> 每页 <span v-text="pageSize"></span>
        </div>
    </div>
{/block}
