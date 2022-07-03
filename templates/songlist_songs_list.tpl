<{if $results|default:''}>
    <div class="sl_pagenav">
        <{$pagenav}>
    </div>
    <!--<h2 ><{$smarty.const._MD_SONGLIST_RESULTS}></h2>-->
    <table class="sl_resulttable" cellpadding="3">
        <thead>
        <tr>
            <th class="id"><{$smarty.const._MD_SONGLIST_RESULTS_SONGID}></th>
            <th class="title"><{$smarty.const._MD_SONGLIST_RESULTS_TITLE}></th>
            <th class="artist"><{$smarty.const._MD_SONGLIST_RESULTS_ARTIST}></th>
            <th class="mp3"><{$smarty.const._MD_SONGLIST_RESULTS_MP3}></th>
            <{if !isset($wurlf_mobile) or not $wurlf_mobile}>
                <{if $xoConfig.singer|default:''}>
                    <th class="artist"><{$smarty.const._MD_SONGLIST_RESULTS_SINGER}></th>
                <{/if}>
            <{/if}>
        </tr>
        </thead>
        <tbody>
        <{foreach item=result from=$results}>
            <tr class="<{cycle values="even,odd"}>">
                <td class="id"><a href="<{$result.url}>"><{$result.songid}></a></td>
                <td><a href="<{$result.url}>"><{$result.title}></a></td>
                <td>
                    <{foreach from=$result.artists_array key=number item=artist}>
                        <a href="<{$artist.url}>"><{$artist.name}></a>
                        <{if is_array($result.artists|default:'') && $number<sizeof($result.artists)-1}>,&nbsp;
                        <{/if}>
                    <{/foreach}>
                </td>

                <td colspan="2" align="center">
                    <{if $result.mp3}>
                        <{$result.mp3}>
                    <{else}>
                        &nbsp;
                    <{/if}>
                </td>

                <{if !isset($wurlf_mobile) or not $wurlf_mobile}>
                    <{if $xoConfig.singer|default:''}>
                        <td>
                            <{foreach from=$result.artists_array key=number item=artist}>
                                <{if $artist.singer}><{$artist.singer}>
                                    <{if is_array($result.artists|default:'') && $number<sizeof($result.artists)-1}>,&nbsp;
                                    <{/if}>
                                <{/if}>
                            <{/foreach}>
                        </td>
                    <{/if}>
                <{/if}>
            </tr>
            <{if !isset($wurlf_mobile) or not $wurlf_mobile}>
                <{if $xoConfig.lyrics}>
                    <tr class="<{cycle values="even,odd"}>">
                        <td></td>
                        <td colspan='3'>
                            <div class="lyric"><{$result.lyrics}></div>
                        </td>
                    </tr>
                <{/if}>
            <{/if}>
        <{/foreach}>
        </tbody>
    </table>
    <div class="sl_pagenav">
        <{$pagenav}>
    </div>
<{/if}>
