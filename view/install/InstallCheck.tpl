{extends file='install/InstallLayout.tpl'}
{block name='content'}
    <h2>检查文件权限</h2>
    <div class="content">
        {$info|raw}
    </div>
{/block}
{block name='btn'}
    <a class="form-btn" href="{url act='index'}">上一步</a>
    <a class="form-btn red" href="{url act='database'}">下一步</a>
    {if !$ok}
        <a class="form-btn" href="{url act='check'}">重新检查</a>
    {/if}
{/block}