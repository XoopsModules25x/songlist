<script type="text/javascript">
function set_location_url($uri) {
    window.location = $uri;
}
</script>
<!--<h2><{$smarty.const._MD_SONGLIST_BROWSEBY}></h2>-->
<div class="sl_alphadiva">
    <div class="head">
    <{$smarty.const._MD_SONGLIST_BROWSEBY1}>&nbsp;
        <select id='browsetype' name='browsetype'>
            <{if $songs|default:''}>
                <{if $xoConfig.album}>
                    <option value="album"<{if $smarty.get.fct|default:'' eq 'album'}> selected="selected"<{/if}>><{$smarty.const._MD_SONGLIST_SELECTBY_ALBUM}></option>
                <{/if}>
                <option value="artist"<{if $smarty.get.fct|default:'' eq 'artist'}> selected="selected"<{/if}>><{$smarty.const._MD_SONGLIST_SELECTBY_ARTIST}></option>
                <option value="lyrics"<{if $smarty.get.fct|default:'' eq 'lyrics'}> selected="selected"<{/if}>><{$smarty.const._MD_SONGLIST_SELECTBY_LYRICS}></option>
            <{/if}>
            <option value="title"<{if $smarty.get.fct|default:'' eq 'title' or $smarty.get.fct|default:'' eq ''}> selected="selected"<{/if}>><{$smarty.const._MD_SONGLIST_SELECTBY_TITLE}></option>
        </select>
    &nbsp;<{$smarty.const._MD_SONGLIST_BROWSEBY2}>
    </div>
    <div class="sl_alphasubdivb">
        <a class='sl_browsebya' onclick="set_location_url('<{$php_self}>?op=browseby&fct='+$('#browsetype').val()+'&value=A');">A</a>&nbsp;
        <a class='sl_browsebya' onclick="set_location_url('<{$php_self}>?op=browseby&fct='+$('#browsetype').val()+'&value=B');">B</a>&nbsp;
        <a class='sl_browsebya' onclick="set_location_url('<{$php_self}>?op=browseby&fct='+$('#browsetype').val()+'&value=C');">C</a>&nbsp;
        <a class='sl_browsebya' onclick="set_location_url('<{$php_self}>?op=browseby&fct='+$('#browsetype').val()+'&value=D');">D</a>&nbsp;
        <a class='sl_browsebya' onclick="set_location_url('<{$php_self}>?op=browseby&fct='+$('#browsetype').val()+'&value=Đ');">Đ</a>&nbsp;
        <a class='sl_browsebya' onclick="set_location_url('<{$php_self}>?op=browseby&fct='+$('#browsetype').val()+'&value=E');">E</a>&nbsp;
        <a class='sl_browsebya' onclick="set_location_url('<{$php_self}>?op=browseby&fct='+$('#browsetype').val()+'&value=F');">F</a>&nbsp;
        <a class='sl_browsebya' onclick="set_location_url('<{$php_self}>?op=browseby&fct='+$('#browsetype').val()+'&value=G');">G</a>&nbsp;
        <a class='sl_browsebya' onclick="set_location_url('<{$php_self}>?op=browseby&fct='+$('#browsetype').val()+'&value=H');">H</a>&nbsp;
        <a class='sl_browsebya' onclick="set_location_url('<{$php_self}>?op=browseby&fct='+$('#browsetype').val()+'&value=I');">I</a>&nbsp;
        <a class='sl_browsebya' onclick="set_location_url('<{$php_self}>?op=browseby&fct='+$('#browsetype').val()+'&value=J');">J</a>&nbsp;
        <a class='sl_browsebya' onclick="set_location_url('<{$php_self}>?op=browseby&fct='+$('#browsetype').val()+'&value=K');">K</a>&nbsp;
        <a class='sl_browsebya' onclick="set_location_url('<{$php_self}>?op=browseby&fct='+$('#browsetype').val()+'&value=L');">L</a>&nbsp;
        <a class='sl_browsebya' onclick="set_location_url('<{$php_self}>?op=browseby&fct='+$('#browsetype').val()+'&value=M');">M</a>&nbsp;
        <a class='sl_browsebya' onclick="set_location_url('<{$php_self}>?op=browseby&fct='+$('#browsetype').val()+'&value=N');">N</a>&nbsp;
        <a class='sl_browsebya' onclick="set_location_url('<{$php_self}>?op=browseby&fct='+$('#browsetype').val()+'&value=O');">O</a>&nbsp;
        <a class='sl_browsebya' onclick="set_location_url('<{$php_self}>?op=browseby&fct='+$('#browsetype').val()+'&value=P');">P</a>&nbsp;
        <a class='sl_browsebya' onclick="set_location_url('<{$php_self}>?op=browseby&fct='+$('#browsetype').val()+'&value=Q');">Q</a>&nbsp;
        <a class='sl_browsebya' onclick="set_location_url('<{$php_self}>?op=browseby&fct='+$('#browsetype').val()+'&value=R');">R</a>&nbsp;
        <a class='sl_browsebya' onclick="set_location_url('<{$php_self}>?op=browseby&fct='+$('#browsetype').val()+'&value=S');">S</a>&nbsp;
        <a class='sl_browsebya' onclick="set_location_url('<{$php_self}>?op=browseby&fct='+$('#browsetype').val()+'&value=T');">T</a>&nbsp;
        <a class='sl_browsebya' onclick="set_location_url('<{$php_self}>?op=browseby&fct='+$('#browsetype').val()+'&value=U');">U</a>&nbsp;
        <a class='sl_browsebya' onclick="set_location_url('<{$php_self}>?op=browseby&fct='+$('#browsetype').val()+'&value=Ư');">Ư</a>&nbsp;
        <a class='sl_browsebya' onclick="set_location_url('<{$php_self}>?op=browseby&fct='+$('#browsetype').val()+'&value=V');">V</a>&nbsp;
        <a class='sl_browsebya' onclick="set_location_url('<{$php_self}>?op=browseby&fct='+$('#browsetype').val()+'&value=W');">W</a>&nbsp;
        <a class='sl_browsebya' onclick="set_location_url('<{$php_self}>?op=browseby&fct='+$('#browsetype').val()+'&value=X');">X</a>&nbsp;
        <a class='sl_browsebya' onclick="set_location_url('<{$php_self}>?op=browseby&fct='+$('#browsetype').val()+'&value=Y');">Y</a>&nbsp;
        <a class='sl_browsebya' onclick="set_location_url('<{$php_self}>?op=browseby&fct='+$('#browsetype').val()+'&value=Z');">Z</a>&nbsp;
        <a class='sl_browsebya' onclick="set_location_url('<{$php_self}>?op=browseby&fct='+$('#browsetype').val()+'&value=0');">0 - 9</a>
    </div>
</div>
