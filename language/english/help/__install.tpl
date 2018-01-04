<div id="help-template" class="outer">
    <h1 class="head">Help:
        <a class="ui-corner-all tooltip" href="<{$xoops_url}>/modules/<{$smarty.const._MI_WFC_DIRNAME}>/admin/index.php"
           title="Back to the administration of <{$smarty.const._MI_WFC_NAME}>"> <{$smarty.const._MI_WFC_NAME}>
            <img src="<{xoAdminIcons home.png}>"
                 alt="Back to the Administration of <{$smarty.const._MI_WFC_NAME}>"/>
        </a></h1>

    <h4 class="odd">Installation</h4>
    <table>
        <tr>
            <td style="font-family: 'Arial, sans-serif'; background-color: #FF0000;" width="550"><b><span
                    style="color: white;">These instructions
        are for a fresh install only.
        <p>If you want to update an older WF-Channel please select 'Update' instructions.</p>
        </span></b></td>
        </tr>
    </table>
    <span style="font-family: Arial, sans-serif; font-size: smaller; "><span style="color: #FF6600; ">
        <p><span style="text-decoration: underline;"><br>Remember: It is always a good idea to make a database backup before installing any modules.<br></span></p><br>
    </span><span style="font-family: Arial, sans-serif; font-size: 144%;  text-decoration: underline;">Fresh installation of WF-Channel</span> <span
            style="font-size: x-small;">
<br>
<ol><br>
    <li><span style="text-decoration: underline;"><b>Upload the module to your website</b></span>
        <p>Upload the '<i>wfchannel</i>' folder to <i>{xoops-rootdirectory}/modules</i> folder<br></p>
    </li>
    <br><li><span style="text-decoration: underline;"><b>Change and verify folder permissions</b></span>
        <p>CHMOD the following folders to 777: </p>
        <p><i>wfchannel/cache<br>wfchannel/images<br>wfchannel/images/linkimages<br>wfchannel/html<br>wfchannel/html/images<br>
            <br></i></p>
    </li>
    <li><span style="text-decoration: underline;"><b>Install the module</b></span>
        <p>Login as administrator and enter Xoops Administration page. Select <i>System --> modules</i> and install wf-sections<br>
        </p>
    </li><br>
    <li><span style="text-decoration: underline;"><b>Set module permissions</b></span>
        <p>Most important step now will be to setup the group permissions for the module via <i>Xoops: System --> groups</i><br>
            .</p>
    </li>
</ol>
</span></span>

</div>
