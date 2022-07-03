<h1><{$smarty.const._AM_SONGLIST_UTF8MAP_H1}></h1>
<p><{$smarty.const._AM_SONGLIST_UTF8MAP_P}></p>
<div style="clear:both; height:45px;">
    <div style="float:right; height:45px;"><{$pagenav}></div>
</div>

<form action="<{$php_self}>" method='post'>
<table>
    <tr class="head">
        <th><{$utfid_th}></th>
        <th><{$from_th}></th>
        <th><{$to_th}></th>
        <th><{$created_th}></th>
        <th><{$updated_th}></th>
        <th><{$smarty.const._AM_SONGLIST_TH_ACTIONS}></th>
    </tr>
    <tr class="filter">
        <th>&nbsp;</th>
        <th><{$filter_from_th}></th>
        <th><{$filter_to_th}></th>
        <th>&nbsp;</th>
        <th>&nbsp;</th>
        <th>&nbsp;</th>
    </tr>
    <{foreach item=item from=$utf8map|default:null}>
    <tr class="<{cycle values="even,odd"}>">
        <td align='center'><{$item.utfid}><{$item.form.id}></td>
        <td align='center'><{$item.form.from}></td>
        <td align='center'><{$item.form.to}></td>
        <td align='right'><{$item.form.created}></td>
        <td align='right'><{$item.form.updated|default:''}></td>
        <td align='right'><a href="<{$php_self}>?op=utf8map&fct=edit&id=<{$item.utfid}>&start=<{$start}>&limit=<{$limit}>&order=<{$order}>&sort=<{$sort}>&filter=<{$filter}>"><{$smarty.const._EDIT}></a>&nbsp;|&nbsp;<a href="<{$php_self}>?op=utf8map&fct=delete&id=<{$item.utfid}>&start=<{$start}>&limit=<{$limit}>&order=<{$order}>&sort=<{$sort}>&filter=<{$filter}>"><{$smarty.const._DELETE}></a></td>
    </tr>
    <{/foreach}>
    <tr class="foot">
        <td colspan="9"><input type='submit' name='submit' value='<{$smarty.const._SUBMIT}>'></td>
    </tr>
</table>
<input type='hidden' name='op' value='utf8map'>
<input type='hidden' name='fct' value='savelist'>
<input type='hidden' name='start' value='<{$start}>'>
<input type='hidden' name='limit' value='<{$limit}>'>
<input type='hidden' name='order' value='<{$order}>'>
<input type='hidden' name='sort' value='<{$sort}>'>
<input type='hidden' name='filter' value='<{$filter}>'>
</form>
<h1><{$smarty.const._AM_SONGLIST_NEW_UTF8MAP_H1}></h1>
<p><{$smarty.const._AM_SONGLIST_NEW_UTF8MAP_P}></p>
<{$form}>
