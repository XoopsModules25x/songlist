<div id="help-template" class="outer">
    <{include file=$smarty.const._MI_SONGLIST_HELP_HEADER}>

    <h4 class="odd">WF-Channel History</h4>
    <br>
    <p><br><b>1.00 No history yet, just released.</b><br><br></p>
    <p><br><b>1.01 Bug fix and new additions. These will show major issues and additions.</b><br>
    </p>
    <ul>
        <li>Fixed bug where anon users could not see channels within the main menu.</li>
        <br>
        <li>Added refer a friend area to this module</li>
        <br>
        <li>added switches in main config for Anon users to either be allowed access
            to link to us or refer a friend
        </li>
        <br>
        <li>Added backendjs.php for link to us.</li>
        <br>
        <li>Plus others.</li>
    </ul>
    <p><br><b>1.0.2 Bug Fixes and new additions.</b><br></p>
    <ul>
        <li>Fixed no upgrade Script in package.</li>
        <br>
        <li>Fixed missing database field.</li>
        <br>
        <li>Fixed Submenu items not showing correctly.</li>
        <br>
        <li>Fixed upload problems, this error was due to uploader.php error. Spent many
            hours trying to fix this before I found the little bugger.
        </li>
        <br>
        <li>Fixed bug where the files where not correctly CHMOD after upload.</li>
        <br>
        <li>Fixed the Group Access Error, silly me left an echo in there that should
            not have been lol.
        </li>
        <br>
        <li>Removed other small errors.</li>
        <br>
        <li>Removed all the upload fields and place them into one area for easy management.</li>
        <br>
        <li>Added upload files area.</li>
        <br>
        <li>Added reorder channels area.</li>
        <br>
        <li>Added Comments.</li>
        <br>
        <li>Added Search.</li>
        <br>
        <li>Added and removed some cosmetic parts.</li>
        <br>
        <li>Fixed bug where link to us and refer submenu item still displayed for anon
            user even thou they where not allowed.
        </li>
    </ul>
    <p><br><b>1.0.3</b><br></p>
    <ul>
        <li>Not released internal fixes and additions</li>
    </ul>
    <p><br><b>1.0.4</b><br></p>
    <ul>
        <li>Not released internal fixes and additions</li>
    </ul>
    <p><br><b>1.0.5</b><br></p>
    <ul>
        <li>Fixed many of the bugs listed at Xoops.org and that I found myself.</li>
        <br>
        <li>Changed many language defines (Thanks to Horacio Salazar for these).</li>
        <br>
        <li>Added. Can now copy an HTML file contents straight into the database to
            edit at a later stage.
        </li>
        <br>
        <li>Added. You can strip HTML tags from the HTML.</li>
        <br>
        <li>Added. HTML Cleaning. You can now clean up MS Word HTML before saving the
            Channel Page.
        </li>
        <br>
        <li>Added. The path for Images for Pages that use HTML files are changed on
            page load, If the Page has images? Then store these in the html/images folder.
        </li>
        <br>
        <li>Added Publish and Expire dates.</li>
    </ul>
    <p><br><b>1.0.6</b><br></p>
    <ul>
        <li>Fixed Double email sent on refer a friend.</li>
        <br>
        <li>Fixed Navigation error in admin.</li>
    </ul>
    <p><br><b>1.0.7</b><br></p>
    <ul>
        <li>Adding Full Security To Wf-channel to avoid users with module access rights
            to be able to read every page just by manually editing the URL to it.
        </li>
        <li>Bug fixed "Disable Comments" now working correctly.</li>
        <li>Bug fixed "Admin section - rename menu"</li>
        <li>Bug fixed "WF-C 1.06 text formatting bug" fixed where linebreaks were ignored
            and you had to use bb or html tags to do it.
        </li>
        <li>Bug fixed "fixed backendjs.php" for the RSS feed</li>
        <li>Bug fixed "Page Menu Link Not Displayed On Link/refer Pages"</li>
        <li>Change Page Menu Link to a function.</li>
        <li>Add define("_MA_NORIGHTTOVIEWPAGE","You do not have permission to view this
            page"); to main.php
        </li>
        <li>Change in xoops_version.php variable $modversion['dirname'] not used now
            ( $xoopsModule = &amp;$modhandler->getByDirname($modversion['dirname']); )
        </li>
        <li>Now 100% ML hack ( marcan ) compatible, so it is <strong>recommanded</strong>
            not to use the index.php for wfchannel from the ML package
        </li>
        <li>modify link to wfsections2.com in wfchannel admin menu : Help Section, Report
            a bug section to wf-project site
        </li>
        <li>Add [pagebreak] tag</li>
        <li>Bug fixed "Some wrong links in the language files and the upgrade.php"</li>
        <li>Bug fixed "Refer a friend admin parameters : seems that mail default message
            is not taken into account in mail."
        </li>
    </ul>
    </font></div>
