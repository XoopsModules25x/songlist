<h1><{$smarty.const._AM_SONGLIST_SONGS_H1}></h1>
<p><{$smarty.const._AM_SONGLIST_SONGS_P}></p>
<div style="clear:both; height:45px;">
    <div style="float:right; height:45px;"><{$pagenav}></div>
</div>

<form action="<{$php_self}>" method='post'>
<table>
    <tr class="head">
        <th><{$sid_th}></th>
        <th><{$cid_th}></th>
        <{if $xoConfig.album}>
        <th><{$abid_th}></th>
        <{/if}>
        <{if $xoConfig.voice}>
        <th><{$vcid_th}></th>
        <{/if}>
        <th><{$songid_th}></th>
        <th><{$title_th}></th>
        <th><{$hits_th}></th>
        <th><{$rank_th}></th>
        <th><{$mp3_th}></th>
        <th><{$created_th}></th>
        <th><{$updated_th}></th>
        <th><{$smarty.const._AM_SONGLIST_TH_ACTIONS}></th>
    </tr>
    <tr class="filter">
        <th>&nbsp;</th>
        <th><{$filter_cid_th}></th>
        <{if $xoConfig.album}>
        <th><{$filter_abid_th}></th>
        <{/if}>
        <{if $xoConfig.voice}>
        <th><{$filter_vcid_th}></th>
        <{/if}>
        <th><{$filter_songid_th}></th>
        <th><{$filter_title_th}></th>
        <th><{$filter_hits_th}></th>
        <th><{$filter_rank_th}></th>
        <th>&nbsp;</th>
        <th>&nbsp;</th>
        <th>&nbsp;</th>
        <th>&nbsp;</th>
    </tr>
    <{foreach item=item from=$songs}>
    <tr class="<{cycle values="even,odd"}>">
        <td align='center'><{$item.sid}><{$item.form.id}></td>
        <td align='center'><{$item.form.cid}></td>
        <{if $xoConfig.album}>
        <td align='center'><{$item.form.abid}></td>
        <{/if}>
        <{if $xoConfig.voice}>
        <td align='center'><{$item.form.vcid}></td>
        <{/if}>
        <td align='center'><{$item.form.songid}></td>
        <td align='center'><{$item.form.title}></td>
        <td align='center'><{$item.hits}></td>
        <td align='center'><{$item.rank}></td>
        <td align='center'><{if $item.mp3}><{$item.mp3}><{else}><{$item.form.mp3}><{/if}></td>
        <td align='right'><{$item.form.created}></td>
        <td align='right'><{$item.form.updated|default:''}></td>
        <td align='right'><a href="<{$php_self}>?op=category&fct=edit&id=<{$item.sid}>&start=<{$start}>&limit=<{$limit}>&order=<{$order}>&sort=<{$sort}>&filter=<{$filter}>"><{$smarty.const._EDIT}></a>&nbsp;|&nbsp;<a href="<{$php_self}>?op=category&fct=delete&id=<{$item.sid}>&start=<{$start}>&limit=<{$limit}>&order=<{$order}>&sort=<{$sort}>&filter=<{$filter}>"><{$smarty.const._DELETE}></a></td>
    </tr>
    <{/foreach}>
    <tr class="foot">
        <td colspan="12"><input type='submit' name='submit' value='<{$smarty.const._SUBMIT}>'></td>
    </tr>
</table>
<input type='hidden' name='op' value='category'>
<input type='hidden' name='fct' value='savelist'>
<input type='hidden' name='start' value='<{$start}>'>
<input type='hidden' name='limit' value='<{$limit}>'>
<input type='hidden' name='order' value='<{$order}>'>
<input type='hidden' name='sort' value='<{$sort}>'>
<input type='hidden' name='filter' value='<{$filter}>'>
</form>
<h1><{$smarty.const._AM_SONGLIST_NEW_SONGS_H1}></h1>
<p><{$smarty.const._AM_SONGLIST_NEW_SONGS_P}></p>
<{$form}>
