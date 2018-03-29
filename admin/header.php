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
 * @copyright    XOOPS Project https://xoops.org/
 * @license      GNU GPL 2 or later (http://www.gnu.org/licenses/gpl-2.0.html)
 * @package
 * @since
 * @author       XOOPS Development Team, Kazumi Ono (AKA onokazu)
 */

require_once(dirname(dirname(dirname(__DIR__))) . '/include/cp_header.php');

if (!defined('_CHARSET')) {
    define('_CHARSET', 'UTF-8');
}
if (!defined('_CHARSET_ISO')) {
    define('_CHARSET_ISO', 'ISO-8859-1');
}

$GLOBALS['songlistAdmin'] = true;

$GLOBALS['myts'] = \MyTextSanitizer::getInstance();

$moduleHandler                   = xoops_getHandler('module');
$configHandler                   = xoops_getHandler('config');
$GLOBALS['songlistModule']       = $moduleHandler->getByDirname('songlist');
$GLOBALS['songlistModuleConfig'] = $configHandler->getConfigList($GLOBALS['songlistModule']->getVar('mid'));

ini_set('memory_limit', $GLOBALS['songlistModuleConfig']['memory_admin'] . 'M');
set_time_limit($GLOBALS['songlistModuleConfig']['time_admin']);

xoops_load('pagenav');
xoops_load('xoopslists');
xoops_load('xoopsformloader');

require_once $GLOBALS['xoops']->path('class' . DS . 'xoopsmailer.php');

if (file_exists($GLOBALS['xoops']->path('/Frameworks/moduleclasses/moduleadmin/moduleadmin.php'))) {
    require_once $GLOBALS['xoops']->path('/Frameworks/moduleclasses/moduleadmin/moduleadmin.php');
} else {
    echo xoops_error("Error: You don't use the Frameworks \"admin module\". Please install this Frameworks");
}

$GLOBALS['songlistImageIcon']  = \Xmf\Module\Admin::iconUrl('', 16);
$GLOBALS['songlistImageAdmin'] = \Xmf\Module\Admin::iconUrl('', 32);

if ($GLOBALS['xoopsUser']) {
    $modulepermHandler = xoops_getHandler('groupperm');
    if (!$modulepermHandler->checkRight('module_admin', $GLOBALS['songlistModule']->getVar('mid'), $GLOBALS['xoopsUser']->getGroups())) {
        redirect_header(XOOPS_URL, 1, _NOPERM);
    }
} else {
    redirect_header(XOOPS_URL . '/user.php', 1, _NOPERM);
}

xoops_loadLanguage('user');

if (!isset($GLOBALS['xoopsTpl']) || !is_object($GLOBALS['xoopsTpl'])) {
    include_once(XOOPS_ROOT_PATH . '/class/template.php');
    $GLOBALS['xoopsTpl'] = new \XoopsTpl();
}

$GLOBALS['xoopsTpl']->assign('pathImageIcon', $GLOBALS['songlistImageIcon']);
$GLOBALS['xoopsTpl']->assign('pathImageAdmin', $GLOBALS['songlistImageAdmin']);

require_once XOOPS_ROOT_PATH . '/modules/' . $GLOBALS['songlistModule']->getVar('dirname') . '/include/functions.php';
require_once XOOPS_ROOT_PATH . '/modules/' . $GLOBALS['songlistModule']->getVar('dirname') . '/include/songlist.object.php';
require_once XOOPS_ROOT_PATH . '/modules/' . $GLOBALS['songlistModule']->getVar('dirname') . '/include/songlist.form.php';

xoops_loadLanguage('admin', 'songlist');

$GLOBALS['songlistModule'] = $moduleHandler->getByDirname('songlist');
