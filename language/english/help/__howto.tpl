<div id="help-template" class="outer">
    <h1 class="head">Help:
        <a class="ui-corner-all tooltip" href="<{$xoops_url}>/modules/<{$smarty.const._MI_WFC_DIRNAME}>/admin/index.php"
           title="Back to the administration of <{$smarty.const._MI_WFC_NAME}>"> <{$smarty.const._MI_WFC_NAME}>
            <img src="<{xoAdminIcons home.png}>"
                 alt="Back to the Administration of <{$smarty.const._MI_WFC_NAME}>"/>
        </a></h1>

    <h4 class="odd">How to Use: WF-Channel</h4>
    <span style="font-family: Arial, sans-serif; font-size: x-small; ">WF-Channel as eight area's of WF-Channel admin area:
        <ol>
            <li>General Settings</li>
            <li>Main Admin page.</li>
            <li>Create new Channel.</li>
            <li>Link Page Admin.</li>
            <li>Group Permissions</li>
            <li>Refer a friend Admin.</li>
            <li>Upload File.</li>
            <li>Reorder Channels.</li>
        </ol>
    </span>
    <h4 class="odd">1. General Settings</h4>
    <br>
    The general settings allow you to change some aspects of WF-Channels Configuration. Such as: <strong><br><br>a. HTML
    Upload
    Directory:</strong> <br>This is the directory where your static html pages will be uploaded and stored. This is for
    use
    within channel contents. <strong><br><br>b. Image Upload Directory<br></strong>This is the directory where your
    channel
    logo image will be uploaded and stored. <strong><br><br>c. Link Image Upload Directory</strong> <br>This is the
    directory
    where your 'Link to us' images will be uploaded and stored. <strong><br><br>d. Maximum upload size</strong> <br>The
    maximum
    size limited allowed when uploading a file. Covers both images and html files. Default is 50000kb <strong><br><br>e.
    Maximum
    upload Image width</strong> <br>The maximum image width size allowed when uploading an image. Default is 600px
    <strong>
        <br><br>f. Maximum upload Image height</strong> <br>The maximum image height size allowed when uploading an
    image. Default
    is 600px <strong><br><br>g. Maximum number of Channels displayed on each page</strong> <br>The maximum number of
    channels
    to be displayed in the Main Admin Page. <strong><br><br>h. Allow anonymous users access to Link to Us?</strong> <br>Using
    this switch will allow Anon users to view this channel <strong><br><br>i. Allow anonymous users access to Refer a
    Friend?</strong>
    <br>Using this switch will allow Anon users to view this channel <strong><br><br>j. Comment Rules</strong> <br>Allows
    you
    to change the way comments are dealt with when submitted <strong><br><br>k. Allow anonymous post in
    comments?</strong>
    <br>Allow anon users to post comments
    <h4 class="odd">2. Main Admin page</h4>
    <pre><br>
    Within this area you will be able to modify or delete current channels, you will also be able to view some information regarding each channel such as:

    <strong>ID</strong>: The Channel ID
    <strong>Page Title</strong>: The title used for this channel.
    <strong>Weight</strong>: The channel weight, the order in which it is listed in either the main menu or main page.
    <strong>Default Page:</strong> Shows which channel is the default channel.
    <strong>Main Page Link</strong>: Shows which channel will be listed in the main default page.
    <strong>Submenu Item:</strong> Shows which channel will be listed in the main menu as a sub-menu.
    <strong>Action</strong>: Allows you to Modify or Delete a channel.
    </pre>
    <h4 class="odd">3. Create new Channel</h4>
    <pre>
    This area will allow you to create a new Channel. The following option can be used to create this new Channel:

    <strong>a. Channel Logo:</strong>
    This is the image to be used for the logo of each channel page.

   <strong> b. Channel Weight:</strong>
    The weight of the Channel in the main menu or default channel page.

    <strong>c. Channel Title:</strong>
    The title used if a channel will be displayed within the main menu as a sub-item.

    d. Channel headline:
    The Title to be displayed within each channel page.

    <strong>e. Static HTML:</strong>
    A static HTML file that will be used as the channel content, this will override the 'page content' option below.

    <strong>f. Channel Content:</strong>
    This option is used to enter the channel content. This can be straight text, html or X-code.

    <strong>g. Disable HTML Tags:</strong>
    Disable all HTML code in the channel page content.

   <strong> h. Disable Smiley Icons:</strong>
    Disable all Smiley code in the channel page content.

   <strong> i. Disable XOOPS Codes:</strong>
    Disable all XOOPS Codes in the channel page content.

    <strong>j. Use Linebreak Conversion:</strong>
    Use this option if you are not using HTML code for the Page content, else switch off if HTML code is used.

    <strong>k. Set as Default Channel:</strong>
    Sets the Channel page that will be the default when clicking the WFChannel link in the main menu. This must be set.

    <strong>l. Add as a submenu item:</strong>
    Choose this option to display the channel in the main menu as a sub item.

    <strong>m. Add link to the main page:</strong>
    Choose this option to display the Channel as a link within the default page.

    <strong>n. Allow Comments for this channel:</strong>
    Allow users to submit and view comments for this channel.
    </pre>
    <h4 class="odd">4. Link Page Admin</h4>
    <pre>
    This area of the WF-Channel Admin will allow you to configure the 'Link to us page'.

    <strong>a. Channel Logo:</strong>
    You can choose the image to be used as the 'Link to us' page logo; this is displayed at the top of the page.

   <strong> b. Channel Title:</strong>
    The title used if a channel will be displayed within the main menu as a sub-item.

   <strong> c. Title of the Text Link:</strong>
    You can choose the title to be used as the text link title

    <strong>d. Image for Button link:</strong>
    The image to be used as your button link.

    <strong>e. Image for Logo link:</strong>
    The image to be used as your logo link.

    <strong>f. Image for Banner link:</strong>
    The image to be used as your banner link.

    <strong>g. Add news feed option to link page:</strong>
    Allow your users to have the option of adding an RSS new feed to their website.

    <strong>h. Add as a submenu item?:</strong>
    This option will allow you to add a 'link to Us' item in the main menu as a sub-item.

    <strong>i. Add link to the main page?:</strong>
    This option will allow you to add a 'link to Us' item in the default page as an item link.
    </pre>
    <h4 class="odd">5. Group Permissions</h4>
    <pre>
    These sets the group access permissions for each channel, uncheck or check the option for each channel permission.

    <strong>Link to Us</strong> and <strong>Refer to a friend</strong> are not controlled by Group permissions at this stage, you can however allow anon user
    to view these channels via WF-Channel configuration. Either on/off.
    </pre>
    <h4 class="odd">6. Refer a friend Admin</h4>
    <pre>
    This area of the WF-Channel Admin will allow you to configure the 'refer a friend' channel.

    <strong>a. Channel Logo:</strong>
    You can choose the image to be used as the 'refer a friend' page logo; this is displayed at the top of the page.

    <strong>b. Channel Title:</strong>
    You can choose the title to be used for this channel

   <strong> c. Channel headline:</strong>
    You can enter extra text as a short introduction, this can be HTML code, Xoops Code or plain text

    <strong>d. Use Senders Stored Email address?</strong>
    Setting this will make WF-Channel look for an email address for the sender stored within xoops. If no email
    address is found it will default to a blank text box

    <strong>e. Allow User to enter own Message?</strong>
    Setting this will allow your visitors to enter there own message when sending a refer a friend email,
    else to text box will be shown.

    <strong>f. Enter default message:</strong>
    Enter a default message to be sent with the refer a friend email.

    <strong>g. Add as a submenu item:</strong>
    This option will allow you to add a 'refer a friend' item in the main menu as a sub-item.

   <strong> h. Add link to the main page:</strong>
    This option will allow you to add a 'refer a friend' item in the main menu as a sub-item.

   <strong> NOTICE:</strong> I have not shown option between c &amp; d, as these have already been covered in part 3.

    <strong>TIP:</strong>
    If you do not wish to have a message with the email, set 'Allow User to enter own Message' and
    leave 'Enter default message:' blank.
    </pre>
    <h4 class="odd">7. Upload File</h4>
    <pre>
    Ok, this one is a little bit different and is a bit hard to explain, but here I go :-)

    This upload area is dynamic, in such that it will show different parts for each selection.

    When you first enter the Upload file area you will be shown one selection box with three choices.
    These choices represent the different upload area's defines in the configuration. You MUST select a field before
    the other parts of this are shown!

    <strong>1. The main select box</strong>
    This will allow you to choose which area your file will be uploaded to

    <strong>2. File viewer</strong>
    Depending on the choice in one, you will either be shown the image or HTML select box.
    The image select box will allow you to view the selected image. (I will add an option to view HTML files in the next version).

    <strong>3. Upload field</strong>
    This will allow you to choose and image or HTML file for uploading.

    <strong>4. Buttons</strong>
    The submit button will allow you to the file to the directory chosen in option 1.
    The delete button will allow you to delete the file selected in option 2.
    </pre>
    <h4 class="odd">8. Reorder Channels</h4>
    <pre>
    This will allow you to easily and quickly reorder the channel weight, use the input text box to enter the
    new weight and click on the submit button.      </pre>
</div>
