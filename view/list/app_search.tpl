{extends file='layout/list.tpl'}
{block name="title"}字段管理{/block}
{block name="caption"}工具-字段管理{/block}
{block name='header'}
    <script src="{url ctl='Res' f="field-list.js"}"></script>
{/block}

{block name='list-header'}
    <div class="yee-list-header">
        <div class="yee-caption"><a class="yee-back" href="{url act='index' ctl='AppList'  appId=$this->get('appId')}"><i class="icofont-reply"></i></a> 工具-搜索字段管理</div>
        <div class="yee-toolbar">
            <span> 共 <span id="records-count">0</span> 条记录</span>
            <a href="javascript:window.location.reload()" class="refresh-btn"><i class="icofont-refresh"></i>刷新</a>
            <a id="add-btn" href="{url act='add' listId=$this->listId  appId=$this->get('appId')}" class="yee-btn red"><i class="icofont-patient-file"></i>添加字段</a>
        </div>
    </div>
{/block}

{block name='list-search'}
    <div class="yee-list-search">
        <div>
            列表：<span class="org" style="font-size: 14px; font-weight: normal">{$listRow.title}</span>
            表名：<input class="blue" style="border: none;width: 1px; opacity: 0" value="@pf_{$listRow.tbName}"/><a href="javascript:;" style="font-size: 14px;" class="v-copy"><i class="icofont-copy"></i>
                @pf_{$listRow.tbName}</a>
        </div>
        <div>
            <form id="searchForm" yee-module="search-form" data-bind="#list">
                <div class="yee-cell">
                    <label class="yee-section-label"><em></em>字段名称：</label>
                    <span><input name="name" class="form-inp text" type="text"/></span>
                </div>
                <div class="yee-cell">
                    <input id="listId" name="listId" type="hidden" value="{$this->listId}"/>
                    <input class="form-btn blue" value="查询" type="submit"/>
                    <input class="form-btn normal" value="重置" type="reset"/>
                </div>
                <div class="yee-cell fr">
                    <a href="{url act='selectField' listId=$this->listId}" class="yee-btn blue"
                       yee-module="dialog" data-width="1000" data-height="600"
                       on-success="$('#list').emit('reload');"
                    ><i class="icofont icofont-copy-alt"></i>从表单模型拷贝</a>
                    <a id="copy-btn" href="javascript:;" data-type="search" class="yee-btn"><i class="icofont-copy"></i>拷贝</a>
                    <a id="paste-btn" data-url="{url act='paste'}" data-type="search" href="javascript:;" class="yee-btn"><i class="icofont-copy-invert"></i>黏贴</a>
                    <a id="add-del" href="{url act='deleteChoice'}" yee-module="confirm ajax choice" on-success="$('#list').emit('reload');" data-confirm@msg="确定要删除所选条目了吗？"
                       class="yee-btn select-all red2" style="margin-right: 20px"><i class="icofont-bin"></i>删除所选</a>
                    <a id="add-btn" href="{url ctl='AppList' act='index'}" class="yee-btn blue">返回列表</a>
                    <a href="{url ctl='AppList' act='code' id=$this->listId}" class="yee-btn" target="_blank"><i class="icofont-code"></i>代码</a>
                    {*<a href="{$listRow.testUrl}" class="yee-btn green" target="_blank"><i class="icofont-paint"></i>测试</a>*}
                </div>
            </form>
        </div>
    </div>
{/block}

{block name='list-table'}
    <table id="list" class="yee-datatable" yee-module="datatable" data-auto-load="true" width="100%">
        <thead>
        <tr>
            <th width="40"><input type="checkbox"></th>
            <th width="40" data-order="id">ID</th>
            <th width="180" align="left" data-order="name">数据库字段</th>
            <th align="left">字段名称</th>
            <th width="180">字段类型</th>
            <th width="180">所属TAB</th>
            <th width="30" align="left" data-order="sort">排序</th>
            <th width="240" data-fixed="right">操作</th>
        </tr>
        </thead>
        <tbody yee-template>
        <tr v-for="rs in list">
            <td align="center" v-html="rs._choice"></td>
            <td align="center" v-html="rs.id"></td>
            <td v-html="rs.name"></td>
            <td v-html="rs.label"></td>
            <td align="center" v-html="rs.type"></td>
            <td align="center" v-html="rs.tabIndex"></td>
            <td v-html="rs._sort"></td>
            <td align="right" class="opt-btns" v-html="rs._operate"></td>
        </tr>
        <tr v-if="list.length==0">
            <td colspan="100">没有任何数据！</td>
        </tr>
        </tbody>
    </table>
{/block}

