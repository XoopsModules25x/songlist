<?php declare(strict_types=1);

use Xmf\Module\Admin;
use Xmf\Request;
use XoopsModules\Songlist\Helper;
use XoopsModules\Songlist\VoiceHandler;
use XoopsModules\Songlist\Form\FormController;

require __DIR__ . '/header.php';

xoops_loadLanguage('admin', 'songlist');

xoops_cp_header();

$op     = $_REQUEST['op'] ?? 'voice';
$fct    = $_REQUEST['fct'] ?? 'list';
$limit  = Request::getInt('limit', 30, 'REQUEST');
$start  = Request::getInt('start', 0, 'REQUEST');
$order  = !empty($_REQUEST['order']) ? $_REQUEST['order'] : 'DESC';
$sort   = !empty($_REQUEST['sort']) ? '' . $_REQUEST['sort'] . '' : 'created';
$filter = !empty($_REQUEST['filter']) ? '' . $_REQUEST['filter'] . '' : '1,1';

switch ($op) {
    default:
    case 'voice':
        switch ($fct) {
            default:
            case 'list':
                $adminObject = Admin::getInstance();
                $adminObject->displayNavigation(basename(__FILE__));

                /** @var VoiceHandler $voiceHandler */
                $voiceHandler = Helper::getInstance()->getHandler('Voice');

                $criteria        = $voiceHandler->getFilterCriteria($GLOBALS['filter']);
                $ttl             = $voiceHandler->getCount($criteria);
                $GLOBALS['sort'] = !empty($_REQUEST['sort']) ? '' . $_REQUEST['sort'] . '' : 'created';

                $pagenav = new \XoopsPageNav($ttl, $GLOBALS['limit'], $GLOBALS['start'], 'start', 'limit=' . $GLOBALS['limit'] . '&sort=' . $GLOBALS['sort'] . '&order=' . $GLOBALS['order'] . '&op=' . $GLOBALS['op'] . '&fct=' . $GLOBALS['fct'] . '&filter=' . $GLOBALS['filter']);
                $GLOBALS['xoopsTpl']->assign('pagenav', $pagenav->renderNav());

                foreach ($voiceHandler->filterFields() as $id => $key) {
                    $GLOBALS['xoopsTpl']->assign(
                        \mb_strtolower(str_replace('-', '_', $key) . '_th'),
                        '<a href="'
                        . $_SERVER['SCRIPT_NAME']
                        . '?start='
                        . $GLOBALS['start']
                        . '&limit='
                        . $GLOBALS['limit']
                        . '&sort='
                        . $key
                        . '&order='
                        . (($key == $GLOBALS['sort']) ? ('DESC' === $GLOBALS['order'] ? 'ASC' : 'DESC') : $GLOBALS['order'])
                        . '&op='
                        . $GLOBALS['op']
                        . '&filter='
                        . $GLOBALS['filter']
                        . '">'
                        . (defined('_AM_SONGLIST_TH_' . \mb_strtoupper(str_replace('-', '_', $key))) ? constant('_AM_SONGLIST_TH_' . \mb_strtoupper(str_replace('-', '_', $key))) : '_AM_SONGLIST_TH_' . \mb_strtoupper(str_replace('-', '_', $key)))
                        . '</a>'
                    );
                    $GLOBALS['xoopsTpl']->assign('filter_' . \mb_strtolower(str_replace('-', '_', $key)) . '_th', $voiceHandler->getFilterForm($GLOBALS['filter'], $key, $GLOBALS['sort'], $GLOBALS['op'], $GLOBALS['fct']));
                }

                $GLOBALS['xoopsTpl']->assign('limit', $GLOBALS['limit']);
                $GLOBALS['xoopsTpl']->assign('start', $GLOBALS['start']);
                $GLOBALS['xoopsTpl']->assign('order', $GLOBALS['order']);
                $GLOBALS['xoopsTpl']->assign('sort', $GLOBALS['sort']);
                $GLOBALS['xoopsTpl']->assign('filter', $GLOBALS['filter']);
                $GLOBALS['xoopsTpl']->assign('xoConfig', $GLOBALS['songlistModuleConfig']);

                $criteria->setStart($GLOBALS['start']);
                $criteria->setLimit($GLOBALS['limit']);
                $criteria->setSort('`' . $GLOBALS['sort'] . '`');
                $criteria->setOrder($GLOBALS['order']);

                $voices = $voiceHandler->getObjects($criteria, true);
                foreach ($voices as $cid => $voice) {
                    if (is_object($voice)) {
                        $GLOBALS['xoopsTpl']->append('voice', $voice->toArray());
                    }
                }
                $GLOBALS['xoopsTpl']->assign('form', FormController::getFormVoice(false));
                $GLOBALS['xoopsTpl']->assign('php_self', $_SERVER['SCRIPT_NAME']);
                $GLOBALS['xoopsTpl']->display('db:songlist_cpanel_voice_list.tpl');
                break;
            case 'new':
            case 'edit':
                $adminObject = Admin::getInstance();
                $adminObject->displayNavigation(basename(__FILE__));

                $voiceHandler = Helper::getInstance()->getHandler('Voice');
                if (Request::hasVar('id', 'REQUEST')) {
                    $voice = $voiceHandler->get(Request::getInt('id', 0, 'REQUEST'));
                } else {
                    $voice = $voiceHandler->create();
                }

                $GLOBALS['xoopsTpl']->assign('form', $voice->getForm());
                $GLOBALS['xoopsTpl']->assign('php_self', $_SERVER['SCRIPT_NAME']);
                $GLOBALS['xoopsTpl']->display('db:songlist_cpanel_voice_edit.tpl');
                break;
            case 'save':
                $voiceHandler = Helper::getInstance()->getHandler('Voice');
                $id           = 0;
                $id           = Request::getInt('id', 0, 'REQUEST');
                if ($id) {
                    $voice = $voiceHandler->get($id);
                } else {
                    $voice = $voiceHandler->create();
                }
                $voice->setVars($_POST[$id]);

                if (!$id = $voiceHandler->insert($voice)) {
                    redirect_header($_SERVER['SCRIPT_NAME'] . '?op=' . $GLOBALS['op'] . '&fct=list&limit=' . $GLOBALS['limit'] . '&start=' . $GLOBALS['start'] . '&order=' . $GLOBALS['order'] . '&sort=' . $GLOBALS['sort'] . '&filter=' . $GLOBALS['filter'], 10, _AM_SONGLIST_MSG_VOICE_FAILEDTOSAVE);
                    exit(0);
                }
                if ('new' === $_REQUEST['state'][$_REQUEST['id']]) {
                    redirect_header(
                        $_SERVER['SCRIPT_NAME'] . '?op=' . $GLOBALS['op'] . '&fct=edit&id=' . $_REQUEST['id'] . '&limit=' . $GLOBALS['limit'] . '&start=' . $GLOBALS['start'] . '&order=' . $GLOBALS['order'] . '&sort=' . $GLOBALS['sort'] . '&filter=' . $GLOBALS['filter'],
                        10,
                        _AM_SONGLIST_MSG_VOICE_SAVEDOKEY
                    );
                } else {
                    redirect_header($_SERVER['SCRIPT_NAME'] . '?op=' . $GLOBALS['op'] . '&fct=list&limit=' . $GLOBALS['limit'] . '&start=' . $GLOBALS['start'] . '&order=' . $GLOBALS['order'] . '&sort=' . $GLOBALS['sort'] . '&filter=' . $GLOBALS['filter'], 10, _AM_SONGLIST_MSG_VOICE_SAVEDOKEY);
                }
                exit(0);

                break;
            case 'savelist':
                $voiceHandler = Helper::getInstance()->getHandler('Voice');
                foreach ($_REQUEST['id'] as $id) {
                    $voice = $voiceHandler->get($id);
                    $voice->setVars($_POST[$id]);
                    if (!$voiceHandler->insert($voice)) {
                        redirect_header(
                            $_SERVER['SCRIPT_NAME'] . '?op=' . $GLOBALS['op'] . '&fct=list&limit=' . $GLOBALS['limit'] . '&start=' . $GLOBALS['start'] . '&order=' . $GLOBALS['order'] . '&sort=' . $GLOBALS['sort'] . '&filter=' . $GLOBALS['filter'],
                            10,
                            _AM_SONGLIST_MSG_VOICE_FAILEDTOSAVE
                        );
                        exit(0);
                    }
                }
                redirect_header($_SERVER['SCRIPT_NAME'] . '?op=' . $GLOBALS['op'] . '&fct=list&limit=' . $GLOBALS['limit'] . '&start=' . $GLOBALS['start'] . '&order=' . $GLOBALS['order'] . '&sort=' . $GLOBALS['sort'] . '&filter=' . $GLOBALS['filter'], 10, _AM_SONGLIST_MSG_VOICE_SAVEDOKEY);
                exit(0);
                break;
            case 'delete':
                $voiceHandler = Helper::getInstance()->getHandler('Voice');
                $id           = 0;
                if (Request::hasVar('id', 'POST') && $id = Request::getInt('id', 0, 'POST')) {
                    $voice = $voiceHandler->get($id);
                    if (!$voiceHandler->delete($voice)) {
                        redirect_header(
                            $_SERVER['SCRIPT_NAME'] . '?op=' . $GLOBALS['op'] . '&fct=list&limit=' . $GLOBALS['limit'] . '&start=' . $GLOBALS['start'] . '&order=' . $GLOBALS['order'] . '&sort=' . $GLOBALS['sort'] . '&filter=' . $GLOBALS['filter'],
                            10,
                            _AM_SONGLIST_MSG_VOICE_FAILEDTODELETE
                        );
                        exit(0);
                    }
                    redirect_header($_SERVER['SCRIPT_NAME'] . '?op=' . $GLOBALS['op'] . '&fct=list&limit=' . $GLOBALS['limit'] . '&start=' . $GLOBALS['start'] . '&order=' . $GLOBALS['order'] . '&sort=' . $GLOBALS['sort'] . '&filter=' . $GLOBALS['filter'], 10, _AM_SONGLIST_MSG_VOICE_DELETED);
                    exit(0);
                }
                $voice = $voiceHandler->get(Request::getInt('id', 0, 'REQUEST'));
                xoops_confirm(
                    ['id' => $_REQUEST['id'], 'op' => $_REQUEST['op'], 'fct' => $_REQUEST['fct'], 'limit' => $_REQUEST['limit'], 'start' => $_REQUEST['start'], 'order' => $_REQUEST['order'], 'sort' => $_REQUEST['sort'], 'filter' => $_REQUEST['filter']],
                    $_SERVER['SCRIPT_NAME'],
                    sprintf(_AM_SONGLIST_MSG_VOICE_DELETE, $voice->getVar('name'))
                );

                break;
        }
        break;
}

xoops_cp_footer();
