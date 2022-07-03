<?php declare(strict_types=1);
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
 * @license        {@link https://www.gnu.org/licenses/gpl-2.0.html GNU GPL 2 or later}
 * @author         XOOPS Development Team
 */

use Xmf\Module\Admin;
use Xmf\Request;
use XoopsModules\Songlist\Common\TestdataButtons;
use XoopsModules\Songlist\Helper;
use XoopsModules\Songlist\Utility;
use XoopsModules\Songlist\Common\Configurator;

/** @var Admin $adminObject */
/** @var Helper $helper */
/** @var Utility $utility */

require __DIR__ . '/header.php';
xoops_loadLanguage('admin', 'songlist');

xoops_cp_header();

//check for upload folders, create if needed
$configurator = new Configurator();
foreach (array_keys($configurator->uploadFolders) as $i) {
    $utility::createFolder($configurator->uploadFolders[$i]);
    $adminObject->addConfigBoxLine($configurator->uploadFolders[$i], 'folder');
}

$op = (!empty($_GET['op']) ? $_GET['op'] : (!empty($_POST['op']) ? $_POST['op'] : 'default'));

switch ($op) {
    case 'default':
    default:
        $adminObject = Admin::getInstance();
        $adminObject->displayNavigation(basename(__FILE__));

        $adminObject = Admin::getInstance();

        $categoryHandler = Helper::getInstance()->getHandler('Category');
        $artistsHandler  = Helper::getInstance()->getHandler('Artists');
        $albumsHandler   = Helper::getInstance()->getHandler('Albums');
        $genreHandler    = Helper::getInstance()->getHandler('Genre');
        $voiceHandler    = Helper::getInstance()->getHandler('Voice');
        $songsHandler    = Helper::getInstance()->getHandler('Songs');
        $requestsHandler = Helper::getInstance()->getHandler('Requests');
        $votesHandler    = Helper::getInstance()->getHandler('Votes');

        $adminObject->addInfoBox(_AM_SONGLIST_COUNT);
        $adminObject->addInfoBoxLine(sprintf('<label>' . _AM_SONGLIST_NUMBER_OF_CATEGORY . '</label>', $categoryHandler->getCount(null, true)), '', 'green');
        $adminObject->addInfoBoxLine(sprintf('<label>' . _AM_SONGLIST_NUMBER_OF_ARTISTS . '</label>', $artistsHandler->getCount(null, true)), '', 'green');
        $adminObject->addInfoBoxLine(sprintf('<label>' . _AM_SONGLIST_NUMBER_OF_ALBUMS . '</label>', $albumsHandler->getCount(null, true)), '', 'green');
        $adminObject->addInfoBoxLine(sprintf('<label>' . _AM_SONGLIST_NUMBER_OF_GENRE . '</label>', $genreHandler->getCount(null, true)), '', 'green');
        $adminObject->addInfoBoxLine(sprintf('<label>' . _AM_SONGLIST_NUMBER_OF_VOICE . '</label>', $voiceHandler->getCount(null, true)), '', 'green');
        $adminObject->addInfoBoxLine(sprintf('<label>' . _AM_SONGLIST_NUMBER_OF_SONGS . '</label>', $songsHandler->getCount(null, true)), '', 'green');
        $adminObject->addInfoBoxLine(sprintf('<label>' . _AM_SONGLIST_NUMBER_OF_REQUESTS . '</label>', $requestsHandler->getCount(null, true)), '', 'green');
        $adminObject->addInfoBoxLine(sprintf('<label>' . _AM_SONGLIST_NUMBER_OF_VOTES . '</label>', $votesHandler->getCount(null, true)), '', 'green');

        //------------- Test Data Buttons ----------------------------
        if ($helper->getConfig('displaySampleButton')) {
            TestdataButtons::loadButtonConfig($adminObject);
            $adminObject->displayButton('left', '');
        }
        $op = Request::getString('op', 0, 'GET');
        switch ($op) {
            case 'hide_buttons':
                TestdataButtons::hideButtons();
                break;
            case 'show_buttons':
                TestdataButtons::showButtons();
                break;
        }
        //------------- End Test Data Buttons ----------------------------

        $adminObject->displayIndex();

        xoops_cp_footer();
        break;
}
