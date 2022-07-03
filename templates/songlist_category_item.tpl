<{if $category.picture|default:''}>
<div class="sl_maincatpic">
    <a href="<{$category.url}>"><img src="<{$category.picture}>" width='90%'></a>
</div>
<{/if}>
<div class="sl_maincat">
    <a href="<{$category.url|default:''}>"><{$category.name|default:''}></a><{if $category.parenturl|default:''}> <a href="<{$category.parenturl}>"><em><{$smarty.const._MD_SONGLIST_GOTOPARENT}></em></a><{/if}>
</div>
<{if $category.subcategories|default:''}>
<div class="sl_subcat">
    <{assign var=cats value=0}>
    <{foreach from=$category.subcategories item=subcategory}>
    <{assign var=cats value=$cats+1}>
    <a href="<{$subcategory.url}>"><{$subcategory.name}></a><{if $cats < count($category.subcategories)}>,&nbsp;<{/if}>
    <{/foreach}>
</div>
<{/if}>
