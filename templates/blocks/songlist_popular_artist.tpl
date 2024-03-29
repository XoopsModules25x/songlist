<{if $block}>
    <div style='font-size:1.15em; clear:both;'>
        <{if $block.title|default:''}>
        <{$block.title}>
        <{elseif $block.name}>
        <{$block.name}>
        <{/if}>
    </div>
    <{if $block.categories_array|is_array && count($block.categories_array) > 0 }>
    <div style='font-size:0.85em; clear:both;'>
        <{$smarty.const._BL_SONGLIST_CATEGORIES}>
    </div>
    <div style='font-size:0.65em; clear:both;'>
        <{foreach from=$block.categories_array key=number item=category}>
            <{$category.name}><{if $number<sizeof($block.categories_array)-1}>,&nbsp;<{/if}>
        <{/foreach}>
    </div>
    <{elseif $block.category}>
    <div style='font-size:0.85em; clear:both;'>
        <{$smarty.const._BL_SONGLIST_CATEGORY}>
    </div>
    <div style='font-size:0.65em; clear:both;'>
        <{$block.category.title}>
    </div>
    <{/if}>
    <{if $block.genres|default:''|is_array && count($block.genres) > 0 }>
    <div style='font-size:0.85em; clear:both;'>
        <{$smarty.const._BL_SONGLIST_GENRES}>
    </div>
    <div style='font-size:0.65em; clear:both;'>
        <{foreach from=$block.genres key=number item=genre}>
            <{$genre.name}><{if ($number<sizeof($block.genres)-1)}>,&nbsp;<{/if}>
        <{/foreach}>
    </div>
    <{elseif $block.genre|default:''}>
    <div style='font-size:0.85em; clear:both;'>
        <{$smarty.const._BL_SONGLIST_GENRE}>
    </div>
    <div style='font-size:0.65em; clear:both;'>
        <{$block.genre.name}></a>
    </div>
    <{/if}>
    <{if $block.artists_array|default:''|is_array && count($block.artists_array) > 0 }>
    <div style='font-size:0.85em; clear:both;'>
        <{$smarty.const._BL_SONGLIST_ARTISTS}>
    </div>
    <div style='font-size:0.65em; clear:both;'>
        <{foreach from=$block.artists_array key=number item=artist}>
            <a href="<{$artist.url}>"><{$artist.name}></a><{if $number<sizeof($block.artists_array)-1}>,&nbsp;<{/if}>
        <{/foreach}>
    </div>
    <{elseif isset($block.artist)}>
    <div style='font-size:0.85em; clear:both;'>
        <{$smarty.const._BL_SONGLIST_ARTIST}>
    </div>
    <div style='font-size:0.65em; clear:both;'>
        <a href="<{$block.artist.url}>"><{$block.artist.title}></a>
    </div>
    <{/if}>
    <{if $block.albums_array|default:''|is_array && count($block.albums_array) > 0}>
    <div style='font-size:0.85em; clear:both;'>
        <{$smarty.const._BL_SONGLIST_ALBUMS}>
    </div>
    <div style='font-size:0.65em; clear:both;'>
        <{foreach from=$block.albums_array key=number item=album}>
            <a href="<{$album.url}>"><{$album.title}></a><{if $number<sizeof($block.albums_array)-1}>,&nbsp;<{/if}>
        <{/foreach}>
    </div>
    <{elseif isset($block.album)}>
    <div style='font-size:0.85em; clear:both;'>
        <{$smarty.const._BL_SONGLIST_ALBUM}>
    </div>
    <div style='font-size:0.65em; clear:both;'>
        <a href="<{$block.album.url}>"><{$block.album.title}></a>
    </div>
    <{/if}>
    <{if $block.songs_array|default:''|is_array && count($block.songs_array) > 0 }>
    <div style='font-size:0.85em; clear:both;'>
        <{$smarty.const._BL_SONGLIST_SONGS}>
    </div>
    <div style='font-size:0.65em; clear:both;'>
        <{foreach from=$block.songs_array key=number item=song}>
            <a href="<{$song.url}>"><{$song.title}></a><{if $number<sizeof($block.songs_array)-1}>,&nbsp;<{/if}>
        <{/foreach}>
    </div>
    <{elseif isset($block.song)}>
    <div style='font-size:0.85em; clear:both;'>
        <{$smarty.const._BL_SONGLIST_SONG}>
    </div>
    <div style='font-size:0.65em; clear:both;'>
        <a href="<{$block.song.url}>"><{$block.song.title}></a>
    </div>
    <{/if}>
<{/if}>
