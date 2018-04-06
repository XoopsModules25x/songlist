<?php

include('header.php');

xoops_loadLanguage('admin', 'songlist');

xoops_cp_header();

$op     = isset($_REQUEST['op']) ? $_REQUEST['op'] : 'requests';
$fct    = isset($_REQUEST['fct']) ? $_REQUEST['fct'] : 'lists';
$limit  = \Xmf\Request::getInt('limit', 30, 'REQUEST');
$start  = \Xmf\Request::getInt('start', 0, 'REQUEST');
$order  = !empty($_REQUEST['order']) ? $_REQUEST['order'] : 'DESC';
$sort   = !empty($_REQUEST['sort']) ? '' . $_REQUEST['sort'] . '' : 'created';
$filter = !empty($_REQUEST['filter']) ? '' . $_REQUEST['filter'] . '' : '1,1';

switch ($op) {
    default:
    case 'requests':
        switch ($fct) {
            default:
            case 'list':
                $adminObject = \Xmf\Module\Admin::getInstance();
                $adminObject->displayNavigation(basename(__FILE__));

                $requestsHandler = xoops_getModuleHandler('requests', 'songlist');

                $criteria        = $requestsHandler->getFilterCriteria($GLOBALS['filter']);
                $ttl             = $requestsHandler->getCount($criteria);
                $GLOBALS['sort'] = !empty($_REQUEST['sort']) ? '' . $_REQUEST['sort'] . '' : 'created';

                $pagenav = new \XoopsPageNav($ttl, $GLOBALS['limit'], $GLOBALS['start'], 'start', 'limit=' . $GLOBALS['limit'] . '&sort=' . $GLOBALS['sort'] . '&order=' . $GLOBALS['order'] . '&op=' . $GLOBALS['op'] . '&fct=' . $GLOBALS['fct'] . '&filter=' . $GLOBALS['filter']);
                $GLOBALS['xoopsTpl']->assign('pagenav', $pagenav->renderNav());

                foreach ($requestsHandler->filterFields() as $id => $key) {
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
                    $GLOBALS['xoopsTpl']->assign('filter_' . strtolower(str_replace('-', '_', $key)) . '_th', $requestsHandler->getFilterForm($GLOBALS['filter'], $key, $GLOBALS['sort'], $GLOBALS['op'], $GLOBALS['fct']));
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

                $requestss = $requestsHandler->getObjects($criteria, true);
                foreach ($requestss as $cid => $requests) {
                    if (is_object($requests)) {
                        $GLOBALS['xoopsTpl']->append('requests', $requests->toArray());
                    }
                }
                $GLOBALS['xoopsTpl']->assign('form', songlist_requests_get_form(false));
                $GLOBALS['xoopsTpl']->assign('php_self', $_SERVER['PHP_SELF']);
                $GLOBALS['xoopsTpl']->display('db:songlist_cpanel_requests_list.html');
                break;

            case 'new':
            case 'edit':

                $adminObject = \Xmf\Module\Admin::getInstance();
                $adminObject->displayNavigation(basename(__FILE__));

                $requestsHandler = xoops_getModuleHandler('requests', 'songlist');
                if (isset($_REQUEST['id'])) {
                    $requests = $requestsHandler->get(\Xmf\Request::getInt('id', 0, 'REQUEST'));
                } else {
                    $requests = $requestsHandler->create();
                }

                $GLOBALS['xoopsTpl']->assign('form', $requests->getForm());
                $GLOBALS['xoopsTpl']->assign('php_self', $_SERVER['PHP_SELF']);
                $GLOBALS['xoopsTpl']->display('db:songlist_cpanel_requests_edit.html');
                break;
            case 'save':

                $requestsHandler = xoops_getModuleHandler('requests', 'songlist');
                $id              = 0;
                if ($id = \Xmf\Request::getInt('id', 0, 'REQUEST')) {
                    $requests = $requestsHandler->get($id);
                } else {
                    $requests = $requestsHandler->create();
                }
                $requests->setVars($_POST[$id]);

                if (!$id = $requestsHandler->insert($requests)) {
                    redirect_header($_SERVER['PHP_SELF'] . '?op=' . $GLOBALS['op'] . '&fct=list&limit=' . $GLOBALS['limit'] . '&start=' . $GLOBALS['start'] . '&order=' . $GLOBALS['order'] . '&sort=' . $GLOBALS['sort'] . '&filter=' . $GLOBALS['filter'], 10, _AM_SONGLIST_MSG_REQUESTS_FAILEDTOSAVE);
                    exit(0);
                } else {
                    if ('new' === $_REQUEST['state'][$_REQUEST['id']]) {
                        redirect_header(
                            $_SERVER['PHP_SELF'] . '?op=' . $GLOBALS['op'] . '&fct=edit&id=' . $_REQUEST['id'] . '&limit=' . $GLOBALS['limit'] . '&start=' . $GLOBALS['start'] . '&order=' . $GLOBALS['order'] . '&sort=' . $GLOBALS['sort'] . '&filter=' . $GLOBALS['filter'],
                            10,
                                        _AM_SONGLIST_MSG_REQUESTS_SAVEDOKEY
                        );
                    } else {
                        redirect_header($_SERVER['PHP_SELF'] . '?op=' . $GLOBALS['op'] . '&fct=list&limit=' . $GLOBALS['limit'] . '&start=' . $GLOBALS['start'] . '&order=' . $GLOBALS['order'] . '&sort=' . $GLOBALS['sort'] . '&filter=' . $GLOBALS['filter'], 10, _AM_SONGLIST_MSG_REQUESTS_SAVEDOKEY);
                    }
                    exit(0);
                }
                break;
            case 'savelist':

                $requestsHandler = xoops_getModuleHandler('requests', 'songlist');
                foreach ($_REQUEST['id'] as $id) {
                    $requests = $requestsHandler->get($id);
                    $requests->setVars($_POST[$id]);
                    if (!$requestsHandler->insert($requests)) {
                        redirect_header(
                            $_SERVER['PHP_SELF'] . '?op=' . $GLOBALS['op'] . '&fct=list&limit=' . $GLOBALS['limit'] . '&start=' . $GLOBALS['start'] . '&order=' . $GLOBALS['order'] . '&sort=' . $GLOBALS['sort'] . '&filter=' . $GLOBALS['filter'],
                            10,
                                        _AM_SONGLIST_MSG_REQUESTS_FAILEDTOSAVE
                        );
                        exit(0);
                    }
                }
                redirect_header($_SERVER['PHP_SELF'] . '?op=' . $GLOBALS['op'] . '&fct=list&limit=' . $GLOBALS['limit'] . '&start=' . $GLOBALS['start'] . '&order=' . $GLOBALS['order'] . '&sort=' . $GLOBALS['sort'] . '&filter=' . $GLOBALS['filter'], 10, _AM_SONGLIST_MSG_REQUESTS_SAVEDOKEY);
                exit(0);
                break;
            case 'delete':

                $requestsHandler = xoops_getModuleHandler('requests', 'songlist');
                $id              = 0;
                if (isset($_POST['id']) && $id = \Xmf\Request::getInt('id', 0, 'POST')) {
                    $requests = $requestsHandler->get($id);
                    if (!$requestsHandler->delete($requests)) {
                        redirect_header(
                            $_SERVER['PHP_SELF'] . '?op=' . $GLOBALS['op'] . '&fct=list&limit=' . $GLOBALS['limit'] . '&start=' . $GLOBALS['start'] . '&order=' . $GLOBALS['order'] . '&sort=' . $GLOBALS['sort'] . '&filter=' . $GLOBALS['filter'],
                            10,
                                        _AM_SONGLIST_MSG_REQUESTS_FAILEDTODELETE
                        );
                        exit(0);
                    } else {
                        redirect_header($_SERVER['PHP_SELF'] . '?op=' . $GLOBALS['op'] . '&fct=list&limit=' . $GLOBALS['limit'] . '&start=' . $GLOBALS['start'] . '&order=' . $GLOBALS['order'] . '&sort=' . $GLOBALS['sort'] . '&filter=' . $GLOBALS['filter'], 10, _AM_SONGLIST_MSG_REQUESTS_DELETED);
                        exit(0);
                    }
                } else {
                    $requests = $requestsHandler->get(\Xmf\Request::getInt('id', 0, 'REQUEST'));
                    xoops_confirm(
                        ['id' => $_REQUEST['id'], 'op' => $_REQUEST['op'], 'fct' => $_REQUEST['fct'], 'limit' => $_REQUEST['limit'], 'start' => $_REQUEST['start'], 'order' => $_REQUEST['order'], 'sort' => $_REQUEST['sort'], 'filter' => $_REQUEST['filter']],
                        $_SERVER['PHP_SELF'],
                        sprintf(_AM_SONGLIST_MSG_REQUESTS_DELETE, $requests->getVar('name'))
                    );
                }
                break;
        }
        break;

}

xoops_cp_footer();
