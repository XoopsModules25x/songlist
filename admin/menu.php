<?php

use XoopsModules\Songlist;

// require_once  dirname(__DIR__) . '/class/Helper.php';
//require_once  dirname(__DIR__) . '/include/common.php';
$helper = Songlist\Helper::getInstance();

$pathIcon32 = \Xmf\Module\Admin::menuIconPath('');
$pathModIcon32 = $helper->getModule()->getInfo('modicons32');


$moduleHandler                   = xoops_getHandler('module');
$configHandler                   = xoops_getHandler('config');
$GLOBALS['songlistModule']       = $moduleHandler->getByDirname('songlist');
$GLOBALS['songlistModuleConfig'] = $configHandler->getConfigList($GLOBALS['songlistModule']->getVar('mid'));

$adminmenu[] = [
    'title' => _MI_SONGLIST_ADMENU_DASHBOARD,
    'icon'  => '../../' . $GLOBALS['songlistModule']->getInfo('icons32') . '/home.png',
    'image' => '../../' . $GLOBALS['songlistModule']->getInfo('icons32') . '/home.png',
    'link'  => 'admin/dashboard.php',
];

$adminmenu[] = [
    'title' => _MI_SONGLIST_ADMENU_CATEGORY,
    'icon'  => 'images/icons/32/songlist.category.png',
    'image' => 'images/icons/32/songlist.category.png',
    'link'  => 'admin/category.php',
];

if ($GLOBALS['songlistModuleConfig']['voice']) {
    $adminmenu[] = [
        'title' => _MI_SONGLIST_ADMENU_VOICE,
        'icon'  => 'images/icons/32/songlist.voice.png',
        'image' => 'images/icons/32/songlist.voice.png',
        'link'  => 'admin/voice.php',
    ];
}

if ($GLOBALS['songlistModuleConfig']['album']) {
    $adminmenu[] = [
        'title' => _MI_SONGLIST_ADMENU_ALBUMS,
        'icon'  => 'images/icons/32/songlist.albums.png',
        'image' => 'images/icons/32/songlist.albums.png',
        'link'  => 'admin/albums.php',
    ];
}

if ($GLOBALS['songlistModuleConfig']['genre']) {
    $adminmenu[] = [
        'title' => _MI_SONGLIST_ADMENU_GENRE,
        'icon'  => 'images/icons/32/songlist.genre.png',
        'image' => 'images/icons/32/songlist.genre.png',
        'link'  => 'admin/genre.php',
    ];
}

$adminmenu[] = [
    'title' => _MI_SONGLIST_ADMENU_ARTISTS,
    'icon'  => 'images/icons/32/songlist.artists.png',
    'image' => 'images/icons/32/songlist.artists.png',
    'link'  => 'admin/artists.php',
];

$adminmenu[] = [
    'title' => _MI_SONGLIST_ADMENU_SONGS,
    'icon'  => 'images/icons/32/songlist.songs.png',
    'image' => 'images/icons/32/songlist.songs.png',
    'link'  => 'admin/songs.php',
];

$adminmenu[] = [
    'title' => _MI_SONGLIST_ADMENU_VOTE,
    'icon'  => 'images/icons/32/songlist.votes.png',
    'image' => 'images/icons/32/songlist.votes.png',
    'link'  => 'admin/votes.php',
];

//$adminmenu[] = [
//'title' =>  _MI_SONGLIST_ADMENU_PERMISSIONS,
//'icon' =>  'images/icons/32/songlist.permissions.png',
//'image' =>  'images/icons/32/songlist.permissions.png',
//'link' =>  "admin/permissions.php",
//];

$adminmenu[] = [
    'title' => _MI_SONGLIST_ADMENU_REQUESTS,
    'icon'  => 'images/icons/32/songlist.requests.png',
    'image' => 'images/icons/32/songlist.requests.png',
    'link'  => 'admin/requests.php',
];

$adminmenu[] = [
    'title' => _MI_SONGLIST_ADMENU_UTF8MAP,
    'icon'  => 'images/icons/32/songlist.utf8map.png',
    'image' => 'images/icons/32/songlist.utf8map.png',
    'link'  => 'admin/utf8map.php',
];

$adminmenu[] = [
    'title' => _MI_SONGLIST_ADMENU_IMPORT,
    'icon'  => 'images/icons/32/songlist.import.png',
    'image' => 'images/icons/32/songlist.import.png',
    'link'  => 'admin/import.php',
];

$adminmenu[] = [
    'title' => _MI_SONGLIST_ADMENU_FIELDS,
    'icon'  => 'images/icons/32/songlist.fields.png',
    'image' => 'images/icons/32/songlist.fields.png',
    'link'  => 'admin/field.php',
];

$adminmenu[] = [
    'title' => _MI_SONGLIST_ADMENU_FIELDSPERMS,
    'icon'  => 'images/icons/32/songlist.field.permissions.png',
    'image' => 'images/icons/32/songlist.field.permissions.png',
    'link'  => 'admin/field_permissions.php',
];

$adminmenu[] = [
    'title' => _MI_SONGLIST_ADMENU_ABOUT,
    'icon'  => '../../' . $GLOBALS['songlistModule']->getInfo('icons32') . '/about.png',
    'image' => '../../' . $GLOBALS['songlistModule']->getInfo('icons32') . '/about.png',
    'link'  => 'admin/about.php',
];
