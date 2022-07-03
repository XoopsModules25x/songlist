<?php declare(strict_types=1);

/** @var \XoopsModuleHandler $moduleHandler */
$moduleHandler = xoops_getHandler('module');
/** @var \XoopsConfigHandler $configHandler */
$configHandler = xoops_getHandler('config');
if (!isset($GLOBALS['songlistModule'])) {
    $GLOBALS['songlistModule'] = $moduleHandler->getByDirname('songlist');
}
if (is_object($GLOBALS['songlistModule'])) {
    if (!isset($GLOBALS['songlistModuleConfig'])) {
        $GLOBALS['songlistModuleConfig'] = $configHandler->getConfigList($GLOBALS['songlistModule']->getVar('mid'));
    }
}

require_once $GLOBALS['xoops']->path('class/xoopsformloader.php');
require_once $GLOBALS['xoops']->path('class/pagenav.php');

//require_once __DIR__ . '/formselectalbum.php';
//require_once __DIR__ . '/formselectartist.php';
//require_once __DIR__ . '/formselectcategory.php';
//require_once __DIR__ . '/formselectgenre.php';
//require_once __DIR__ . '/formselectvoice.php';
//require_once __DIR__ . '/formselectsong.php';

//if (file_exists($GLOBALS['xoops']->path('/modules/tag/include/formtag.php')) && $GLOBALS['songlistModuleConfig']['tags']) {
//    require_once $GLOBALS['xoops']->path('/modules/tag/include/formtag.php');
//}
