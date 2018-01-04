<?php
/*
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */

/**
 * @copyright      {@link https://xoops.org/ XOOPS Project}
 * @license        {@link http://www.gnu.org/licenses/gpl-2.0.html GNU GPL 2 or later}
 * @package
 * @since
 * @author         XOOPS Development Team
 */

include('header.php');
xoops_loadLanguage('admin', 'songlist');

xoops_cp_header();

$op = (!empty($_GET['op']) ? $_GET['op'] : (!empty($_POST['op']) ? $_POST['op'] : 'default'));

switch ($op) {
    case 'default':
    default:

        $adminObject = \Xmf\Module\Admin::getInstance();
        $adminObject->displayNavigation(basename(__FILE__));

        $adminObject = \Xmf\Module\Admin::getInstance();

        $categoryHandler = xoops_getModuleHandler('category', 'songlist');
        $artistsHandler  = xoops_getModuleHandler('artists', 'songlist');
        $albumsHandler   = xoops_getModuleHandler('albums', 'songlist');
        $genreHandler    = xoops_getModuleHandler('genre', 'songlist');
        $voiceHandler    = xoops_getModuleHandler('voice', 'songlist');
        $songsHandler    = xoops_getModuleHandler('songs', 'songlist');
        $requestsHandler = xoops_getModuleHandler('requests', 'songlist');
        $votesHandler    = xoops_getModuleHandler('votes', 'songlist');

        $adminObject->addInfoBox(_AM_SONGLIST_COUNT);
        $adminObject->addInfoBoxLine(sprintf('<label>' . _AM_SONGLIST_NUMBER_OF_CATEGORY . '</label>', $categoryHandler->getCount(null, true)), '', 'green');
        $adminObject->addInfoBoxLine(sprintf('<label>' . _AM_SONGLIST_NUMBER_OF_ARTISTS . '</label>', $artistsHandler->getCount(null, true)), '', 'green');
        $adminObject->addInfoBoxLine(sprintf('<label>' . _AM_SONGLIST_NUMBER_OF_ALBUMS . '</label>', $albumsHandler->getCount(null, true)), '', 'green');
        $adminObject->addInfoBoxLine(sprintf('<label>' . _AM_SONGLIST_NUMBER_OF_GENRE . '</label>', $genreHandler->getCount(null, true)), '', 'green');
        $adminObject->addInfoBoxLine(sprintf('<label>' . _AM_SONGLIST_NUMBER_OF_VOICE . '</label>', $voiceHandler->getCount(null, true)), '', 'green');
        $adminObject->addInfoBoxLine(sprintf('<label>' . _AM_SONGLIST_NUMBER_OF_SONGS . '</label>', $songsHandler->getCount(null, true)), '', 'green');
        $adminObject->addInfoBoxLine(sprintf('<label>' . _AM_SONGLIST_NUMBER_OF_REQUESTS . '</label>', $requestsHandler->getCount(null, true)), '', 'green');
        $adminObject->addInfoBoxLine(sprintf('<label>' . _AM_SONGLIST_NUMBER_OF_VOTES . '</label>', $votesHandler->getCount(null, true)), '', 'green');
        $adminObject->displayIndex();

        xoops_cp_footer();
        break;
}
