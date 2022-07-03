<{if $album.picture|default:''}>
    <div class="sl_artalbum">
        <a href="<{$album.url}>"><img src='<{$album.picture}>' border="0"></a>
    </div>
<{/if}>

<div class="container">
    <div class="label"><{$album.title|default:''}></div>
    <div class="content"><br>

        <table class="sl_resulttable" cellpadding="3">
            <thead>
            <tr>
                <th class="songid"><{$smarty.const._MD_SONGLIST_RESULTS_SONGID}></th>
                <th><{$smarty.const._MD_SONGLIST_RESULTS_TITLE}></th>
                <th><{$smarty.const._MD_SONGLIST_RESULTS_TRAXNO}></th>
            </tr>
            </thead>
            </tbody>
            <{if $album.songs_array|default:''}>
                <{assign var=songs value=0}>
                <{foreach from=$album.songs_array item=song}>
                    <tr class="<{cycle values="even,odd"}>">
                        <{assign var=songs value=$songs+1}>
                        <td class="songid">
                            <{$song.songid}>
                        </td>
                        <td>
                            <a href="<{$song.url}>"><{$song.title}></a>
                        </td>
                        <td class="traxno">
                            <{$song.traxid}>
                        </td>
                    </tr>
                <{/foreach}>

            <{/if}>
            </tbody>
        </table>
    </div>
</div>
<!--


<{if $album.artists_array|default:''}>
<div class="sl_subtitle">
    <{$smarty.const._MD_SONGLIST_ARTISTS}>
</div>
<div class="sl_subartist">
    <{assign var=artists value=0}>
    <{foreach from=$album.artists_array item=artist}>
    <{assign var=artists value=$artists+1}>
    <a href="<{$artist.url}>"><{$artist.name}></a><{if $artists < count($album.artists)}>,&nbsp;<{/if}>
    <{/foreach}>
</div>
<{/if}>
-->
