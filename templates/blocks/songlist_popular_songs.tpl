<{if $block}>
<{assign var=topno value=0}>
<{foreach from=$block item=item}>
<{assign var=topno value=$topno+1}>
<div style='display:block; margin-bottom:4px; clear:both;'>
    <div style="font-size:1.12em; float:left;">
        #<{$topno}>
    </div>
    <div style="font-size:1.02em; float:right;">
        <{if $item.url}><a href="<{$item.url}>"><{/if}><{if $item.name|default:''}><{$item.name}><{elseif $item.title|default:''}><{$item.title}><{/if}><{if $item.url|default:''}></a><{/if}>
    </div>
</div>
<{/foreach}>
<{/if}>
