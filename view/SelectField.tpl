{extends file='layout/layoutList.tpl'}
{block name="title"}字段管理{/block}
{block name="caption"}工具-字段管理{/block}
{block name='header'}
    <script src="{url ctl='r' act='index' file='field-list-js'}"></script>
{/block}


{block name='list-header'}
    <div class="yee-list-header">
        <div class="yee-caption">工具-字段管理</div>
        <div class="yee-toolbar">
            <span> 共 <span id="records-count">0</span> 条记录</span>
            <a href="javascript:window.location.reload()" class="refresh-btn"><i class="icofont-refresh"></i>刷新</a>
        </div>
    </div>
{/block}

{block name='list-search'}
    <div class="yee-list-search">
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
                    <a id="copy-select" href="{url act='copyChoice' listId=$listId}" yee-module="ajax choice" class="yee-btn blue">
                        <i class="icofont-copy-black"></i>选择复制字段
                    </a>
                </div>
            </form>
        </div>
    </div>
{/block}

{block name='list-table'}
    <table id="list" class="yee-datatable" yee-module="datatable" data-auto-load="true" width="100%">
        <thead>
        <tr>

            <th width="40" data-order="id">ID</th>
            <th width="180" align="left" data-order="name">数据库字段</th>
            <th align="left">字段名称</th>
            <th width="180">字段类型</th>
            <th width="180">DB字段</th>
            <th width="180">所属TAB</th>
            <th width="30" align="left" data-order="sort">排序</th>
            <th width="80"><input type="checkbox"></th>
        </tr>
        </thead>
        <tbody yee-template>
        <tr yee-each="list" yee-item="rs">
            <td align="center" :html="rs.id"></td>
            <td :html="rs.name"></td>
            <td :html="rs.label"></td>
            <td align="center" :html="rs.type"></td>
            <td align="center" :html="rs.dbtype"></td>
            <td align="center" :html="rs.tabIndex"></td>
            <td :html="rs.sort"></td>
            <td align="center" :html="rs._choice"></td>
        </tr>
        <tr yee-if="list.length==0">
            <td colspan="100">没有任何数据！</td>
        </tr>
        </tbody>
    </table>
{/block}

{block name='footer'}
{literal}
    <script>
        Yee.readyDialog(function (dialog) {
            $('#copy-select').on('success', function (ev, data) {
                dialog.success(data);
                dialog.close();
            });
        });
    </script>
{/literal}
{/block}