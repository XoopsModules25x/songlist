<div class='sl_breadcrumb'>
    <a href="<{$php_self}>?op=category&fct=home"><{$smarty.const._MD_SONGLIST_HOME}></a><{if $category|default:''}>&nbsp;>>&nbsp;<{$category.name}><{/if}>
</div>
<{if $category|default:''}>
<div class='sl_sep'>&nbsp;</div>
<div class='sl_catdesc'>
    <{$category.description}>
</div>
<div class='sl_sep'>&nbsp;</div>
<{/if}>
<{if $categories}>
<!--<h1><{$smarty.const._MD_SONGLIST_CATEGORIES}></h1>-->
<table>
    <{foreach from=$categories item=row}>
    <tr>
        <{foreach from=$row item=category}>
        <td width="<{$category.width}>%"><{include file="db:songlist_category_item.tpl" category=$category}></td>
        <{/foreach}>
    </tr>
    <{/foreach}>
</table>
<{/if}>
