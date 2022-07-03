<{if $results}>
<{if not $pagenav eq ''}>
<div style='width:100%; clear:both; height:25px;'>
    <div class="sl_pagenav">
        <{$pagenav}>
    </div>
</div>
<{/if}>
<table>
    <{foreach from=$results item=row}>
        <{foreach from=$row item=result}>
        <tr>
            <td width="<{$result.width}>%"><{include file="db:songlist_artists_item.tpl" artist=$result}></td>
            <{/foreach}>
        </tr>
        <{/foreach}>
</table>
<{if not $pagenav eq ''}>
<div style='width:100%; clear:both; height:25px;'>
    <div style='float:left;'>
        <{$pagenav}>
    </div>
</div>
<{/if}>
<{/if}>
