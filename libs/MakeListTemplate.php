<?php


namespace tool\libs;


use beacon\core\DB;
use beacon\core\Util;


class MakeListTemplate
{
    private array|null $list = null;
    private array $out = [];
    private array $hook = [];
    private string $path = '';
    private string $keyName = '';

    /**
     * MakeListTemplate constructor.
     * @param int $listId
     * @throws \beacon\core\DBException
     * @throws \Exception
     */
    public function __construct(int $listId = 0)
    {
        $this->list = DB::getRow('select * from @pf_tool_list where id=?', $listId);
        if ($this->list == null) {
            throw new \Exception('生成错误');
        }
        if ($this->list['withTpl'] != 1) {
            return;
        }
        $appId = intval($this->list['appId']);
        $rootDir = ROOT_DIR;
        $app = DB::getRow('select dirName from @pf_tool_app where id=?', $appId);
        if ($app && !empty($app['dirName'])) {
            if (is_dir($app['dirName'])) {
                $rootDir = $app['dirName'];
            }
        }
        $this->path = Util::path($rootDir, $this->list['namespace'], 'zero/view');
        $this->keyName = $this->list['key'];
        $this->createTemplate();
    }

    private function createTemplate()
    {
        if (empty($this->list['baseLayout'])) {
            $this->out[] = '{extends file=\'layout/list.tpl\'}';
        } else {
            $this->out[] = '{extends file=\'' . $this->list['baseLayout'] . '\'}';
        }
        if ($this->list['title']) {
            $this->out[] = "{block name='title'}{$this->list['title']}{/block}";
        }
        if (!empty($this->list['information'])) {
            $this->out[] = "{block name='list-information'}<div class='yee-information'>{$this->list['information']}</div>{/block}";
        }
        if (!empty($this->list['attention'])) {
            $this->out[] = "{block name='list-attention'}<div class='yee-attention'>{$this->list['attention']}</div>{/block}";
        }
        $this->createListTab();
        $this->createListHeader();
        $this->createListSearch();
        $this->createListTable();
        $this->createPageBar();
        $this->createHead();
        $this->createFoot();
    }

    private function createListTab()
    {
        if (!$this->list['viewUseTab']) {
            return;
        }
        $tabItems = Helper::convertArray($this->list['viewTabs'], []);
        if (count($tabItems) > 0) {
            $this->out[] = '';
            $this->out[] = "{block name='list-tab'}";
            $this->out[] = '<div class="yee-tab">';
            $this->out[] = '<ul yee-module="list-tab">';
            foreach ($tabItems as $idx => $item) {
                if ($item['useCode']) {
                    $this->out[] = $item['code'];
                } else {
                    $item['url'] = Helper::tplUrl($item['url']);
                    $this->out[] = "<li{if \$this->get('tabIndex:i',0)=={$idx}} class=\"curr\"{/if}>";
                    $this->out[] = "<a href=\"{$item['url']}\" data-tab-index='{$idx}'>" . htmlspecialchars($item['name']) . "</a>";
                    $this->out[] = "</li>";
                }
            }
            $this->out[] = '</ul>';
            $this->createTabRight();
            $this->out[] = '</div>';
            $this->out[] = "{/block}";
        } else if (!empty($this->list['viewTabCode'])) {
            $this->out[] = '';
            $this->out[] = "{block name='list-tab'}";
            $this->out[] = '<div class="yee-tab">';
            $this->out[] = $this->list['viewTabCode'];
            $this->createTabRight();
            $this->out[] = '</div>';
            $this->out[] = "{/block}";
        }
    }

    private function createTabRight()
    {
        if (!empty($this->list['viewTabRight'])) {
            $this->out[] = '<div  class="yee-tab-right">';
            $this->out[] = $this->list['viewTabRight'];
            $this->out[] = '</div>';
        }
    }

    private function createListHeader()
    {
        $this->out[] = '';
        $this->out[] = "{block name='list-header'}";
        $this->out[] = '<div class="yee-list-header">';
        if (empty($this->list['caption'])) {
            $this->out[] = '<div class="yee-caption">' . $this->list['title'] . '</div>';
        } else {
            $this->out[] = '<div class="yee-caption">' . $this->list['caption'] . '</div>';
        }
        $this->out[] = '<div class="yee-toolbar">';
        $this->out[] = '<span> 共 <span id="records-count">0</span> 条记录</span>';
        $this->out[] = '<a href="javascript:window.location.reload()" class="refresh-btn"><i class="icofont-refresh"></i>刷新</a>';
        if (isset($this->list['topButtons'])) {
            $topButtons = Helper::convertArray($this->list['topButtons'], []);
            $btnCode = [];
            foreach ($topButtons as $btn) {
                $btnCode[] = $btn['code'];
            }
            $btnHtml = join("\n", $btnCode);
            if ($btnHtml) {
                $this->out[] = $btnHtml;
            }
        }
        $this->out[] = '</div></div>';
        $this->out[] = "{/block}";
    }

    private function getViewFields($tabIndex = null): array
    {
        if (!empty($tabIndex)) {
            $fields = DB::getList('select * from @pf_tool_search where listId=? and tabIndex=? order by sort asc', [$this->list['id'], $tabIndex]);
        } else {
            $fields = DB::getList('select * from @pf_tool_search where listId=? order by sort asc', $this->list['id']);
        }
        //修正显示
        $temp = [];
        foreach ($fields as $field) {
            //隐藏字段
            if ($field['hidden']) {
                continue;
            }
            $name = $field['name'];
            $field['boxName'] = empty($field['boxName']) ? $field['name'] : $field['boxName'];
            $field['boxId'] = empty($field['boxId']) ? $field['boxName'] : $field['boxId'];
            $temp[$name] = $field;
        }
        $fields = $temp;
        $keys = array_keys($fields);
        $temp = [];
        for ($idx = 0, $len = count($keys); $idx < $len; $idx++) {
            $key = $keys[$idx];
            $field = &$fields[$key];
            if ($idx == 0) {
                $field['viewMerge'] = 0;
            }
            //如果这一行合并到上一行
            if ($field['viewMerge'] == -1) {
                if ($idx - 1 >= 0) {
                    $prevField = &$fields[$keys[$idx - 1]];
                    $prevField['next'] = &$field;
                } else {
                    $field['viewMerge'] = 0;
                }
            }
            //合并到下一行
            if ($field['viewMerge'] == 1) {
                if ($idx + 1 < $len) {
                    $nextField = &$fields[$keys[$idx + 1]];
                    $nextField['prev'] = &$field;
                } else {
                    $field['viewMerge'] = 0;
                }
            }
            //不合并
            if ($field['viewMerge'] == 0) {
                $temp[$key] = &$field;
            }
        }
        return $temp;
    }

    private function addSearchItem($box)
    {
        if (isset($box['prev'])) {
            $this->addSearchItem($box['prev']);
        }
        $this->out[] = '{if !$search->getField(' . var_export($box['name'], true) . ')->close}';
        $this->out[] = '<div class="yee-cell">';
        $out[] = '<label>';
        if (isset($box['label'][0]) && $box['label'][0] != '!') {
            $out[] = $box['label'] . '：';
        }
        $out[] = '{input field=$search->getField(' . var_export($box['name'], true) . ')}';
        $out[] = '</label>';
        $this->out[] = join('', $out);
        $this->out[] = '</div>';
        $this->out[] = '{/if}';
        if (isset($box['next'])) {
            $this->addSearchItem($box['next']);
        }
    }

    private function createListSearch()
    {
        //选择区按钮数据
        $selectButtons = (isset($this->list['selectButtons']) ? $this->list['selectButtons'] : null);
        $selectButtons = Helper::convertArray($selectButtons, []);
        $code = [];
        foreach ($selectButtons as $btn) {
            $code[] = $btn['code'];
        }
        $fieldCount = DB::getOne('select count(1) from @pf_tool_search where listId=? order by sort asc', $this->list['id']);
        $this->out[] = '';
        $this->out[] = "{block name='list-search'}";
        if (count($code) > 0 || $fieldCount > 0) {
            $this->out[] = '<div class="yee-list-search">';
            if (!empty($code)) {
                $this->out[] = '<div style="display: table-cell;">';
            }
            if (!empty($this->list['searchTop'])) {
                $this->out[] = '<div>';
                $this->out[] = $this->list['searchTop'];
                $this->out[] = '</div>';
                $this->out[] = '<div>';
            }
            $this->out[] = "{if isset(\$search)}";
            $this->out[] = '<form id="searchForm" yee-module="search-form" data-bind="#list">';
            $fields1 = $this->getViewFields('base');
            foreach ($fields1 as $box) {
                $this->addSearchItem($box);
            }
            $fields2 = $this->getViewFields('senior');
            if (count($fields2) > 0) {
                $this->out[] = '<div class="senior-item">';
                foreach ($fields2 as $box) {
                    $this->out[] = '<div class="form-box" style="display: block;">';
                    if (isset($box['prev'])) {
                        $this->addSearchItem($box['prev']);
                    }
                    $out = [];
                    $out[] = '<label class="form-label">';
                    if (isset($box['label'][0]) && $box['label'][0] != '!') {
                        $out[] = $box['label'] . '：';
                    }
                    $out[] = '{input field=$search->getField(' . var_export($box['name'], true) . ')}';
                    $out[] = '</label>';
                    $this->out[] = join('', $out);
                    if (isset($box['next'])) {
                        $this->addSearchItem($box['next']);
                    }
                    $this->out[] = '</div>';
                }
                $this->out[] = '</div>';
            }

            $this->out[] = '<div class="yee-cell">';
            $this->out[] = '<input class="form-btn blue" value="查询" type="submit"/>';
            $this->out[] = '<input class="form-btn normal" value="重置" type="reset"/><input type="hidden" name="sort">';
            $this->out[] = '{$search->fetchHideBox()}';
            if (count($fields2) > 0) {
                $this->out[] = '<a class="senior-btn" onclick="$(\'.yee-list-search\').toggleClass(\'senior\')">高级搜索<i></i></a>';
            }
            $this->out[] = '</div>';
            $this->out[] = '</form>';
            $this->out[] = '{/if}';
            if (!empty($this->list['searchTop'])) {
                $this->out[] = '</div>';
            }
            if (!empty($code)) {
                $this->out[] = '</div>';
                $this->out[] = '<div class="tr nobr" style="display: table-cell;' . $this->list['selectStyle'] . '">';
                $this->out[] = join("\n", $code);
                $this->out[] = '</div>';
            }
            $this->out[] = '</div>';
        }
        $this->out[] = "{/block}";
    }

    private function createListTable()
    {

        $thCode = [];
        $tdCode = [];
        $index = 0;
        $keyName = '_' . $index;
        //选择项
        if ($this->list['useSelect']) {
            $thCode[] = '<th width="40"><input type="checkbox"></th>';
            $tdCode[] = '<td align="center" v-html="rs.' . $keyName . '"></td>';
            $selectValue = empty($this->list['selectValue']) ? '{$rs.id}' : $this->list['selectValue'];
            $this->hook[] = "{hook fn='{$keyName}' rs=null}" . '<input type="checkbox" name="choice" value="' . $selectValue . '">{/hook}';
        }
        $index++;
        $keyName = '_' . $index;
        $fields = Helper::convertArray($this->list['fields'], []);
        //表单列表
        $len = count($fields);
        foreach ($fields as $idx => $field) {
            $thAttr = [];
            $tdAttr = [];
            if (!empty($field['orderName'])) {
                $useOrder = true;
                $thAttr[] = 'data-order="' . $field['orderName'] . '"';
            }
            if (!empty($field['thAlign'])) {
                $thAttr[] = 'align="' . $field['thAlign'] . '"';
            }
            if (!empty($field['thWidth'])) {
                $thAttr[] = 'width="' . $field['thWidth'] . '"';
                if ($this->list['useTwoLine'] && $idx + 1 != $len) {
                    $tdAttr[] = 'width="' . $field['thWidth'] . '"';
                }
            }
            if (!empty($field['thAttrs'])) {
                $thAttr[] = $field['thAttrs'];
            }
            if (!empty($field['tdAlign'])) {
                $tdAttr[] = 'align="' . $field['tdAlign'] . '"';
            }
            if (!empty($field['tdAttrs'])) {
                $tdAttr[] = $field['tdAttrs'];
            }
            if (isset($field['keyName'])) {
                $field['keyName'] = trim($field['keyName']);
                if (!empty($field['keyName'])) {
                    $keyName = $field['keyName'];
                }
            }
            if ($this->list['useTwoLine'] && $idx + 1 == $len) {
                $tdAttr[] = 'colspan="100"';
            }
            $tdAttr[] = 'v-html="rs.' . $keyName . '"';
            $thAttr = join(' ', $thAttr);
            $tdAttr = join(' ', $tdAttr);
            $thCode[] = '<th ' . $thAttr . '>' . (isset($field['title']) ? $field['title'] : '') . '</th>';
            $tdCode[] = '<td ' . $tdAttr . '></td>';
            $this->hook[] = "{hook fn='{$keyName}' rs=null}" . (isset($field['code']) ? $field['code'] : '') . '{/hook}';
            $index++;
            $keyName = '_' . $index;
        }
        $sedCode = '';
        if ($this->list['useTwoLine']) {
            $sedCode = array_pop($tdCode);
            array_pop($thCode);
        }
        //操作项
        if (!empty($this->list['thTitle'])) {
            $field = $this->list;
            $thAttr = [];
            $tdAttr = [];
            if (!empty($field['thAlign'])) {
                $thAttr[] = 'align="' . $field['thAlign'] . '"';
            }
            if (!empty($field['thWidth'])) {
                $thAttr[] = 'width="' . $field['thWidth'] . '"';
                if ($this->list['useTwoLine']) {
                    $tdAttr[] = 'width="' . $field['thWidth'] . '"';
                }
            }
            if (!empty($field['thAttrs'])) {
                $thAttr[] = $field['thAttrs'];
            }
            if (!empty($field['tdAlign'])) {
                $tdAttr[] = 'align="' . $field['tdAlign'] . '"';
            }
            if (!empty($field['tdAttrs'])) {
                $tdAttr[] = $field['tdAttrs'];
            }

            if (isset($field['thOpName'])) {
                $field['thOpName'] = trim($field['thOpName']);
                if (!empty($field['thOpName'])) {
                    $keyName = $field['thOpName'];
                }
            }
            $tdAttr[] = 'v-html="rs.' . $keyName . '"';
            $thAttr = join(' ', $thAttr);
            $tdAttr = join(' ', $tdAttr);
            $thCode[] = '<th ' . $thAttr . '>' . (isset($field['thTitle']) ? $field['thTitle'] : '') . '</th>';
            $tdCode[] = '<td ' . $tdAttr . '></td>';

            $listButtons = (isset($field['listButtons']) ? $field['listButtons'] : null);
            $listButtons = Helper::convertArray($listButtons, []);
            $code = [];
            foreach ($listButtons as $btn) {
                $code[] = $btn['code'];
            }
            $this->hook[] = "{hook fn='{$keyName}' rs=null}" . (join("\n", $code)) . '{/hook}';
        }
        //生成表格
        $this->out[] = '';
        $this->out[] = "{block name='list-table'}";
        $table = [];
        $table[] = '<table id="list" width=100% class="yee-datatable" yee-module="datatable" data-auto-load="true"';
        if (!empty($this->list['listResize']) && $this->list['listResize'] == 1) {
            $table[] = ' data-resize="true"';
            if (!empty($this->list['leftFixed'])) {
                $table[] = ' data-left-fixed="' . $this->list['leftFixed'] . '"';
            }
            if (!empty($this->list['rightFixed'])) {
                $table[] = ' data-right-fixed="' . $this->list['rightFixed'] . '"';
            }
        }
        if (!empty($this->list['listRewrite']) && $this->list['listRewrite'] == 1) {
            $table[] = ' data-rewrite="true"';
        }
        $table[] = '>';
        $this->out[] = join('', $table);
        $this->out[] = '<thead>';
        $this->out[] = '<tr>';
        $this->out[] = join("\n", $thCode);
        $this->out[] = '</tr>';
        $this->out[] = '</thead>';
        $this->out[] = '<tbody yee-template="vue">';
        if ($this->list['useTwoLine']) {
            $this->out[] = '<tr v-for="rs in list"><td colspan="100" style="padding: 0;"><table class="yee-inner-table" cellspacing="0" cellpadding="0" border="0">';
            $this->out[] = '<tr class="first-line">';
            $this->out[] = join("\n", $tdCode);
            $this->out[] = '</tr>';
            $this->out[] = '<tr class="second-line">';
            $this->out[] = $sedCode;
            $this->out[] = '</tr>';
            $this->out[] = '</table></td></tr>';
        } else {
            $this->out[] = '<tr  v-for="rs in list">';
            $this->out[] = join("\n", $tdCode);
            $this->out[] = '</tr>';
        }
        $this->out[] = '<tr v-if="list.length==0"><td colspan="100"> 没有任何数据信息....</td></tr>';
        $this->out[] = '</tbody>';
        $this->out[] = '</table>';
        $this->out[] = "{/block}";
    }

    public function createPageBar()
    {

        if ($this->list['usePageList']) {
            $this->out[] = '';
            $this->out[] = "{block name='list-pagebar'}";
            $this->out[] = '<div yee-module="pagebar" data-bind="#list" class="yee-pagebar">';
            $this->out[] = '    <div yee-template="vue">';
            $this->out[] = '        <div class="pagebar" v-html="barCode"></div>';
            $this->out[] = '        <div class="pagebar-info">共有信息：<span v-text="recordsCount"></span> 页次：<span v-text="page"></span>/<span v-text="pageCount"></span> 每页<span v-text="pageSize"></span></div>';
            $this->out[] = '    </div>';
            $this->out[] = '</div>';
            $this->out[] = '{/block}';
        }
    }

    private function createFoot()
    {
        if (!empty($this->list['footTemplate'])) {
            $this->out[] = '';
            $this->out[] = "{block name='footer'}";
            $this->out[] = $this->list['footTemplate'];
            $this->out[] = "{/block}";
        }
    }

    private function createHead()
    {
        if (!empty($this->list['headTemplate'])) {
            $this->out[] = '';
            $this->out[] = "{block name='header'}";
            $this->out[] = $this->list['headTemplate'];
            $this->out[] = "{/block}";
        }
    }

    public function getCode(): string
    {
        return join("\n", $this->out);
    }

    public function getHookCode(): string
    {
        $out = [];
        $out[] = join("\n", $this->hook);
        return join("\n", $out);
    }

    public function makeFile()
    {
        if (isset($this->list['withTpl']) && $this->list['withTpl'] == 1) {
            $path = Util::path($this->path, 'list');
            Util::makeDir($path);
            $code = '{*Created by Beacon AI Tool4.0. Date:' . date('Y-m-d H:i:s') . '*}' . $this->getCode();
            file_put_contents(Util::path($path, Util::toUnder($this->keyName) . '.tpl'), $code);

            $code = '{*Created by Beacon AI Tool4.0. Date:' . date('Y-m-d H:i:s') . '*}' . $this->getHookCode();
            $path = Util::path($this->path, 'hook');
            Util::makeDir($path);
            file_put_contents(Util::path($path, Util::toUnder($this->keyName) . '.hook.tpl'), $code);
        }
    }

    public static function make(int $listId = 0)
    {
        $maker = new static($listId);
        $maker->makeFile();
    }
}