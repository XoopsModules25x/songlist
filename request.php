<?php declare(strict_types=1);

use XoopsModules\Songlist\Form\SelectCategoryForm;
use XoopsModules\Songlist\Form\SelectGenreForm;
use XoopsModules\Songlist\Helper;
use XoopsModules\Songlist\RequestsHandler;
use XoopsModules\Songlist\Form\FormController;

require_once __DIR__ . '/header.php';

global $file, $op, $fct, $id, $value, $gid, $cid, $singer, $start, $limit;

$category_element = new SelectCategoryForm('', 'cid', ($_GET['cid'] ?? $cid));
$genre_element    = new SelectGenreForm('', 'gid', $gid);
$genre_element    = new SelectGenreForm('', 'vid', $vid);
//$singer_element = new \XoopsModules\Songlist\Form\SelectSinger('', 'singer', $singer);

/** @var RequestsHandler $requestsHandler */
$requestsHandler = Helper::getInstance()->getHandler('Requests');

switch ($op) {
    default:
    case 'request':
        $url = $requestsHandler->getURL();
        if (!mb_strpos($url, $_SERVER['REQUEST_URI']) && empty($_POST)) {
            header('HTTP/1.1 301 Moved Permanently');
            header('Location: ' . $url);
            exit(0);
        }

        switch ($fct) {
            case 'save':
                if (checkEmail($_POST[0]['email'], true) && !empty($_POST[0]['email'])) {
                    $request = $requestsHandler->create();
                    $request->setVars($_POST[0]);
                    $rid = $requestsHandler->insert($request);
                    if ($rid) {
                        redirect_header($_SERVER['SCRIPT_NAME'] . "?op=item&fct=list&id=$id&value=$value&start=$start&limit=$limit", 10, _MD_SONGLIST_MSG_REQUESTSENT);
                    } else {
                        redirect_header($_SERVER['SCRIPT_NAME'] . "?op=item&fct=list&id=$id&value=$value&start=$start&limit=$limit", 10, _MD_SONGLIST_MSG_REQUESTNOTSENT);
                    }
                    exit;
                    break;
                }
                $error = _MD_SONGLIST_MSG_EMAILNOTSET;

            // no break
            default:
            case 'list':
                $GLOBALS['xoopsOption']['template_main'] = 'songlist_requests_index.tpl';
                require_once $GLOBALS['xoops']->path('/header.php');
                if ($GLOBALS['songlistModuleConfig']['force_jquery'] && !isset($GLOBALS['loaded_jquery'])) {
                    $GLOBALS['xoTheme']->addScript(XOOPS_URL . _MI_SONGLIST_JQUERY, ['type' => 'text/javascript']);
                    $GLOBALS['loaded_jquery'] = true;
                }
                $GLOBALS['xoTheme']->addStylesheet(XOOPS_URL . _MI_SONGLIST_STYLESHEET, ['type' => 'text/css']);
                $GLOBALS['xoopsTpl']->assign('xoConfig', $GLOBALS['songlistModuleConfig']);
                $GLOBALS['xoopsTpl']->assign('php_self', $_SERVER['SCRIPT_NAME']);
                $GLOBALS['xoopsTpl']->assign('form', FormController::getFormRequests(false, false));
                if (mb_strlen($error ?? '')) {
                    xoops_error($error);
                }
                require_once $GLOBALS['xoops']->path('/footer.php');
                break;
        }
}
