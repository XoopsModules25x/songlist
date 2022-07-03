<h1><{$smarty.const._AM_SONGLIST_VOTES_H1}></h1>
<p><{$smarty.const._AM_SONGLIST_VOTES_P}></p>
<div style="clear:both; height:45px;">
    <div style="float:right; height:45px;"><{$pagenav}></div>
</div>

<form action="<{$php_self}>" method='post'>
<table>
    <tr class="head">
        <th><{$vid_th}></th>
        <th><{$sid_th}></th>
        <th><{$uid_th}></th>
        <th><{$ip_th}></th>
        <th><{$netaddy_th}></th>
    </tr>
    <tr class="filter">
        <th>&nbsp;</th>
        <th><{$filter_sid_th}></th>
        <th><{$filter_uid_th}></th>
        <th><{$filter_ip_th}></th>
        <th><{$filter_netaddy_th}></th>
    </tr>
    <{foreach item=item from=$votes|default:null}>
    <tr class="<{cycle values="even,odd"}>">
        <td align='center'><{$item.vid}><{$item.form.id}></td>
        <td align='center'><{$item.sid}></td>
        <td align='center'><{$item.uid}></td>
        <td align='center'><{$item.ip}></td>
        <td align='center'><{$item.netaddy}></td>
    </tr>
    <{/foreach}>
    <tr class="foot">
        <td colspan="6">&nbsp;</td>
    </tr>
</table>
<input type='hidden' name='op' value='votes'>
<input type='hidden' name='fct' value='savelist'>
<input type='hidden' name='start' value='<{$start}>'>
<input type='hidden' name='limit' value='<{$limit}>'>
<input type='hidden' name='order' value='<{$order}>'>
<input type='hidden' name='sort' value='<{$sort}>'>
<input type='hidden' name='filter' value='<{$filter}>'>
</form>
<!--
<h1><{$smarty.const._AM_SONGLIST_NEW_VOTES_H1}></h1>
<p><{$smarty.const._AM_SONGLIST_NEW_VOTES_P}></p>
<{$form|default:''}>
 -->
