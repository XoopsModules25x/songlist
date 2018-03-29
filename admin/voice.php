<?php

include('header.php');

xoops_loadLanguage('admin', 'songlist');

xoops_cp_header();

$op     = isset($_REQUEST['op']) ? $_REQUEST['op'] : 'voice';
$fct    = isset($_REQUEST['fct']) ? $_REQUEST['fct'] : 'list';
$limit  = !empty($_REQUEST['limit']) ? (int)$_REQUEST['limit'] : 30;
$start  = !empty($_REQUEST['start']) ? (int)$_REQUEST['start'] : 0;
$order  = !empty($_REQUEST['order']) ? $_REQUEST['order'] : 'DESC';
$sort   = !empty($_REQUEST['sort']) ? '' . $_REQUEST['sort'] . '' : 'created';
$filter = !empty($_REQUEST['filter']) ? '' . $_REQUEST['filter'] . '' : '1,1';

switch ($op) {
    default:
    case 'voice':
        switch ($fct) {
            default:
            case 'list':
                $adminObject = \Xmf\Module\Admin::getInstance();
                $adminObject->displayNavigation(basename(__FILE__));

                $voiceHandler = xoops_getModuleHandler('voice', 'songlist');

                $criteria        = $voiceHandler->getFilterCriteria($GLOBALS['filter']);
                $ttl             = $voiceHandler->getCount($criteria);
                $GLOBALS['sort'] = !empty($_REQUEST['sort']) ? '' . $_REQUEST['sort'] . '' : 'created';

                $pagenav = new \XoopsPageNav($ttl, $GLOBALS['limit'], $GLOBALS['start'], 'start', 'limit=' . $GLOBALS['limit'] . '&sort=' . $GLOBALS['sort'] . '&order=' . $GLOBALS['order'] . '&op=' . $GLOBALS['op'] . '&fct=' . $GLOBALS['fct'] . '&filter=' . $GLOBALS['filter']);
                $GLOBALS['xoopsTpl']->assign('pagenav', $pagenav->renderNav());

                foreach ($voiceHandler->filterFields() as $id => $key) {
                    $GLOBALS['xoopsTpl']->assign(strtolower(str_replace('-', '_', $key) . '_th'), '<a href="'
                                                                                                  . $_SERVER['PHP_SELF']
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
                                                                                                  . (defined('_AM_SONGLIST_TH_' . strtoupper(str_replace('-', '_', $key))) ? constant('_AM_SONGLIST_TH_' . strtoupper(str_replace('-', '_', $key))) : '_AM_SONGLIST_TH_' . strtoupper(str_replace(
                                                                                                      '-',
                                                                                                      '_',
                                                                                                                                                                                                                                                                                                  $key
                                                                                                  )))
                                                                                                  . '</a>');
                    $GLOBALS['xoopsTpl']->assign('filter_' . strtolower(str_replace('-', '_', $key)) . '_th', $voiceHandler->getFilterForm($GLOBALS['filter'], $key, $GLOBALS['sort'], $GLOBALS['op'], $GLOBALS['fct']));
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
                $GLOBALS['xoopsTpl']->assign('form', songlist_voice_get_form(false));
                $GLOBALS['xoopsTpl']->assign('php_self', $_SERVER['PHP_SELF']);
                $GLOBALS['xoopsTpl']->display('db:songlist_cpanel_voice_list.html');
                break;

            case 'new':
            case 'edit':

                $adminObject = \Xmf\Module\Admin::getInstance();
                $adminObject->displayNavigation(basename(__FILE__));

                $voiceHandler = xoops_getModuleHandler('voice', 'songlist');
                if (isset($_REQUEST['id'])) {
                    $voice = $voiceHandler->get((int)$_REQUEST['id']);
                } else {
                    $voice = $voiceHandler->create();
                }

                $GLOBALS['xoopsTpl']->assign('form', $voice->getForm());
                $GLOBALS['xoopsTpl']->assign('php_self', $_SERVER['PHP_SELF']);
                $GLOBALS['xoopsTpl']->display('db:songlist_cpanel_voice_edit.html');
                break;
            case 'save':

                $voiceHandler = xoops_getModuleHandler('voice', 'songlist');
                $id           = 0;
                if ($id = (int)$_REQUEST['id']) {
                    $voice = $voiceHandler->get($id);
                } else {
                    $voice = $voiceHandler->create();
                }
                $voice->setVars($_POST[$id]);

                if (!$id = $voiceHandler->insert($voice)) {
                    redirect_header($_SERVER['PHP_SELF'] . '?op=' . $GLOBALS['op'] . '&fct=list&limit=' . $GLOBALS['limit'] . '&start=' . $GLOBALS['start'] . '&order=' . $GLOBALS['order'] . '&sort=' . $GLOBALS['sort'] . '&filter=' . $GLOBALS['filter'], 10, _AM_SONGLIST_MSG_VOICE_FAILEDTOSAVE);
                    exit(0);
                } else {
                    if ('new' === $_REQUEST['state'][$_REQUEST['id']]) {
                        redirect_header(
                            $_SERVER['PHP_SELF'] . '?op=' . $GLOBALS['op'] . '&fct=edit&id=' . $_REQUEST['id'] . '&limit=' . $GLOBALS['limit'] . '&start=' . $GLOBALS['start'] . '&order=' . $GLOBALS['order'] . '&sort=' . $GLOBALS['sort'] . '&filter=' . $GLOBALS['filter'],
                            10,
                                        _AM_SONGLIST_MSG_VOICE_SAVEDOKEY
                        );
                    } else {
                        redirect_header($_SERVER['PHP_SELF'] . '?op=' . $GLOBALS['op'] . '&fct=list&limit=' . $GLOBALS['limit'] . '&start=' . $GLOBALS['start'] . '&order=' . $GLOBALS['order'] . '&sort=' . $GLOBALS['sort'] . '&filter=' . $GLOBALS['filter'], 10, _AM_SONGLIST_MSG_VOICE_SAVEDOKEY);
                    }
                    exit(0);
                }
                break;
            case 'savelist':

                $voiceHandler = xoops_getModuleHandler('voice', 'songlist');
                foreach ($_REQUEST['id'] as $id) {
                    $voice = $voiceHandler->get($id);
                    $voice->setVars($_POST[$id]);
                    if (!$voiceHandler->insert($voice)) {
                        redirect_header($_SERVER['PHP_SELF'] . '?op=' . $GLOBALS['op'] . '&fct=list&limit=' . $GLOBALS['limit'] . '&start=' . $GLOBALS['start'] . '&order=' . $GLOBALS['order'] . '&sort=' . $GLOBALS['sort'] . '&filter=' . $GLOBALS['filter'], 10, _AM_SONGLIST_MSG_VOICE_FAILEDTOSAVE);
                        exit(0);
                    }
                }
                redirect_header($_SERVER['PHP_SELF'] . '?op=' . $GLOBALS['op'] . '&fct=list&limit=' . $GLOBALS['limit'] . '&start=' . $GLOBALS['start'] . '&order=' . $GLOBALS['order'] . '&sort=' . $GLOBALS['sort'] . '&filter=' . $GLOBALS['filter'], 10, _AM_SONGLIST_MSG_VOICE_SAVEDOKEY);
                exit(0);
                break;
            case 'delete':

                $voiceHandler = xoops_getModuleHandler('voice', 'songlist');
                $id           = 0;
                if (isset($_POST['id']) && $id = (int)$_POST['id']) {
                    $voice = $voiceHandler->get($id);
                    if (!$voiceHandler->delete($voice)) {
                        redirect_header($_SERVER['PHP_SELF'] . '?op=' . $GLOBALS['op'] . '&fct=list&limit=' . $GLOBALS['limit'] . '&start=' . $GLOBALS['start'] . '&order=' . $GLOBALS['order'] . '&sort=' . $GLOBALS['sort'] . '&filter=' . $GLOBALS['filter'], 10, _AM_SONGLIST_MSG_VOICE_FAILEDTODELETE);
                        exit(0);
                    } else {
                        redirect_header($_SERVER['PHP_SELF'] . '?op=' . $GLOBALS['op'] . '&fct=list&limit=' . $GLOBALS['limit'] . '&start=' . $GLOBALS['start'] . '&order=' . $GLOBALS['order'] . '&sort=' . $GLOBALS['sort'] . '&filter=' . $GLOBALS['filter'], 10, _AM_SONGLIST_MSG_VOICE_DELETED);
                        exit(0);
                    }
                } else {
                    $voice = $voiceHandler->get((int)$_REQUEST['id']);
                    xoops_confirm(
                        ['id' => $_REQUEST['id'], 'op' => $_REQUEST['op'], 'fct' => $_REQUEST['fct'], 'limit' => $_REQUEST['limit'], 'start' => $_REQUEST['start'], 'order' => $_REQUEST['order'], 'sort' => $_REQUEST['sort'], 'filter' => $_REQUEST['filter']],
                        $_SERVER['PHP_SELF'],
                        sprintf(_AM_SONGLIST_MSG_VOICE_DELETE, $voice->getVar('name'))
                    );
                }
                break;
        }
        break;

}

xoops_cp_footer();
