<div id="help-template" class="outer">
    <h1 class="head">Help:
        <a class="ui-corner-all tooltip" href="<{$xoops_url}>/modules/<{$smarty.const._MI_WFC_DIRNAME}>/admin/index.php"
           title="Back to the administration of <{$smarty.const._MI_WFC_NAME}>"> <{$smarty.const._MI_WFC_NAME}>
            <img src="<{xoAdminIcons home.png}>"
                 alt="Back to the Administration of <{$smarty.const._MI_WFC_NAME}>"/>
        </a></h1>

    <h4 class="odd">Update</h4>
    <table>
        <tr>
            <td style="font-family: 'Arial, sans-serif'; background-color: #FF0000;" width="550"><b><span
                    style="color: white;">Instructions
        to update an older version of WF-Channel to 1.07
        <p>If you want to do a fresh install of WF-Channel please select 'Install'.</p>
        </span></b></td>
        </tr>
    </table>
    <span style="font-family: Arial, sans-serif; font-size: smaller; "><span style="color: #FF6600; ">
        <p><span style="text-decoration: underline;"><br>Remember: It is always a good idea to make a database backup before installing any modules.</span> <br></p>
    </span>

        <p><span style="font-family: Arial, sans-serif; font-size: 144%; "><span style="text-decoration: underline;"><br>Update from an older WF-Channel version</span><br>&nbsp;</span></p>
<span style="font-size: x-small;">
<ol>
    <li><span style="text-decoration: underline;"><b>Check on the version number of wf-channel that you are currently running.</b></span>

        <p>If you are unsure how to find out: Login as administrator and enter Xoops Administration page. <br>Select <i>System
            --> modules</i> and find the version number in the modules list.</p>

        <p>&nbsp;</p>
    </li>
    <li><span style="text-decoration: underline;"><b>Upload the module to your website</b></span>

        <p>Upload the '<i>wfchannel</i>' folder to <i>{xoops-rootdirectory}/modules</i> folder</p>

        <p>&nbsp;</p>
    </li>
    <li><span style="text-decoration: underline;"><b>Change and verify folder permissions</b></span>

        <p>CHMOD the following folders to 777: </p>

        <p><i>wfchannel/cache<br>wfchannel/images<br>wfchannel/images/linkimages<br>wfchannel/html<br>wfchannel/html/images<br>
        </i></p>

        <p>&nbsp;</p>
    </li>
    <li><span style="text-decoration: underline;"><b>Depending on your current wf-channel version:</b></span><br>- if you are running 1.06: just skip to the next
        step<br>- if you are running 1.05 or lower: run the upgrade script under: &nbsp;&nbsp;&nbsp;
        <a href="http://www.yoursite.tld/modules/wfchannel/upgrade.php">www.yoursite.tld/modules/wfchannel/upgrade.php</a>
        <br><br>
        <br></li>
    <li><span style="text-decoration: underline;"><b>Update the module</b></span>

        <p>Login as administrator and enter Xoops Administration page. Select <i>System --> modules</i> and update wf-channel</p>
    </li>
</ol>
</span></span></div>
