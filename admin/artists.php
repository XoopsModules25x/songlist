<?php declare(strict_types=1);

use Xmf\Module\Admin;
use Xmf\Request;
use XoopsModules\Songlist\{
    Form\FormController,
    Helper,
    Artists,
    ArtistsHandler
};

/** @var Artists $artist */

require __DIR__ . '/header.php';

xoops_loadLanguage('admin', 'songlist');

xoops_cp_header();

$op     = $_REQUEST['op'] ?? 'artists';
$fct    = $_REQUEST['fct'] ?? 'list';
$limit  = Request::getInt('limit', 30, 'REQUEST');
$start  = Request::getInt('start', 0, 'REQUEST');
$order  = !empty($_REQUEST['order']) ? $_REQUEST['order'] : 'DESC';
$sort   = !empty($_REQUEST['sort']) ? '' . $_REQUEST['sort'] . '' : 'created';
$filter = !empty($_REQUEST['filter']) ? '' . $_REQUEST['filter'] . '' : '1,1';

switch ($op) {
    default:
    case 'artists':
        switch ($fct) {
            default:
            case 'list':
                $adminObject = Admin::getInstance();
                $adminObject->displayNavigation(basename(__FILE__));

                /** @var ArtistsHandler $artistsHandler */
                $artistsHandler = Helper::getInstance()->getHandler('Artists');

                $criteria        = $artistsHandler->getFilterCriteria($GLOBALS['filter']);
                $ttl             = $artistsHandler->getCount($criteria);
                $GLOBALS['sort'] = !empty($_REQUEST['sort']) ? '' . $_REQUEST['sort'] . '' : 'created';

                $pagenav = new \XoopsPageNav($ttl, $GLOBALS['limit'], $GLOBALS['start'], 'start', 'limit=' . $GLOBALS['limit'] . '&sort=' . $GLOBALS['sort'] . '&order=' . $GLOBALS['order'] . '&op=' . $GLOBALS['op'] . '&fct=' . $GLOBALS['fct'] . '&filter=' . $GLOBALS['filter']);
                $GLOBALS['xoopsTpl']->assign('pagenav', $pagenav->renderNav());

                foreach ($artistsHandler->filterFields() as $id => $key) {
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
                    $GLOBALS['xoopsTpl']->assign('filter_' . \mb_strtolower(str_replace('-', '_', $key)) . '_th', $artistsHandler->getFilterForm($GLOBALS['filter'], $key, $GLOBALS['sort'], $GLOBALS['op'], $GLOBALS['fct']));
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

                $artistsArray = $artistsHandler->getObjects($criteria, true);
                foreach ($artistsArray as $cid => $artist) {
                    if (is_object($artist)) {
                        $GLOBALS['xoopsTpl']->append('artists', $artist->toArray());
                    }
                }
                $GLOBALS['xoopsTpl']->assign('form', FormController::getFormArtists(false));
                $GLOBALS['xoopsTpl']->assign('php_self', $_SERVER['SCRIPT_NAME']);
                $GLOBALS['xoopsTpl']->display('db:songlist_cpanel_artists_list.tpl');
                break;
            case 'new':
            case 'edit':
                $adminObject = Admin::getInstance();
                $adminObject->displayNavigation(basename(__FILE__));

                $artistsHandler = Helper::getInstance()->getHandler('Artists');
                if (Request::hasVar('id', 'REQUEST')) {
                    $artist = $artistsHandler->get(Request::getInt('id', 0, 'REQUEST'));
                } else {
                    $artist = $artistsHandler->create();
                }

                $GLOBALS['xoopsTpl']->assign('form', $artist->getForm());
                $GLOBALS['xoopsTpl']->assign('php_self', $_SERVER['SCRIPT_NAME']);
                $GLOBALS['xoopsTpl']->display('db:songlist_cpanel_artists_edit.tpl');
                break;
            case 'save':
                $artistsHandler = Helper::getInstance()->getHandler('Artists');
                $id             = 0;
                $id             = Request::getInt('id', 0, 'REQUEST');
                if ($id) {
                    $artist = $artistsHandler->get($id);
                } else {
                    $artist = $artistsHandler->create();
                }
                $artist->setVars($_POST[$id]);

                if (!$id = $artistsHandler->insert($artist)) {
                    redirect_header($_SERVER['SCRIPT_NAME'] . '?op=' . $GLOBALS['op'] . '&fct=list&limit=' . $GLOBALS['limit'] . '&start=' . $GLOBALS['start'] . '&order=' . $GLOBALS['order'] . '&sort=' . $GLOBALS['sort'] . '&filter=' . $GLOBALS['filter'], 10, _AM_SONGLIST_MSG_ARTISTS_FAILEDTOSAVE);
                    exit(0);
                }
                if ('new' === isset($_REQUEST['state']) ? $_REQUEST['state'][$_REQUEST['id']]:'') {
                    redirect_header(
                        $_SERVER['SCRIPT_NAME'] . '?op=' . $GLOBALS['op'] . '&fct=edit&id=' . $_REQUEST['id'] . '&limit=' . $GLOBALS['limit'] . '&start=' . $GLOBALS['start'] . '&order=' . $GLOBALS['order'] . '&sort=' . $GLOBALS['sort'] . '&filter=' . $GLOBALS['filter'],
                        10,
                        _AM_SONGLIST_MSG_ARTISTS_SAVEDOKEY
                    );
                } else {
                    redirect_header($_SERVER['SCRIPT_NAME'] . '?op=' . $GLOBALS['op'] . '&fct=list&limit=' . $GLOBALS['limit'] . '&start=' . $GLOBALS['start'] . '&order=' . $GLOBALS['order'] . '&sort=' . $GLOBALS['sort'] . '&filter=' . $GLOBALS['filter'], 10, _AM_SONGLIST_MSG_ARTISTS_SAVEDOKEY);
                }
                exit(0);

                break;
            case 'savelist':
                $artistsHandler = Helper::getInstance()->getHandler('Artists');
                foreach ($_REQUEST['id'] as $id) {
                    $artist = $artistsHandler->get($id);
                    $artist->setVars($_POST[$id]);
                    if (!$artistsHandler->insert($artist)) {
                        redirect_header(
                            $_SERVER['SCRIPT_NAME'] . '?op=' . $GLOBALS['op'] . '&fct=list&limit=' . $GLOBALS['limit'] . '&start=' . $GLOBALS['start'] . '&order=' . $GLOBALS['order'] . '&sort=' . $GLOBALS['sort'] . '&filter=' . $GLOBALS['filter'],
                            10,
                            _AM_SONGLIST_MSG_ARTISTS_FAILEDTOSAVE
                        );
                        exit(0);
                    }
                }
                redirect_header($_SERVER['SCRIPT_NAME'] . '?op=' . $GLOBALS['op'] . '&fct=list&limit=' . $GLOBALS['limit'] . '&start=' . $GLOBALS['start'] . '&order=' . $GLOBALS['order'] . '&sort=' . $GLOBALS['sort'] . '&filter=' . $GLOBALS['filter'], 10, _AM_SONGLIST_MSG_ARTISTS_SAVEDOKEY);
                exit(0);
                break;
            case 'delete':
                $artistsHandler = Helper::getInstance()->getHandler('Artists');
                $id             = 0;
                if (Request::hasVar('id', 'POST') && $id = Request::getInt('id', 0, 'POST')) {
                    $artist = $artistsHandler->get($id);
                    if (!$artistsHandler->delete($artist)) {
                        redirect_header(
                            $_SERVER['SCRIPT_NAME'] . '?op=' . $GLOBALS['op'] . '&fct=list&limit=' . $GLOBALS['limit'] . '&start=' . $GLOBALS['start'] . '&order=' . $GLOBALS['order'] . '&sort=' . $GLOBALS['sort'] . '&filter=' . $GLOBALS['filter'],
                            10,
                            _AM_SONGLIST_MSG_ARTISTS_FAILEDTODELETE
                        );
                        exit(0);
                    }
                    redirect_header($_SERVER['SCRIPT_NAME'] . '?op=' . $GLOBALS['op'] . '&fct=list&limit=' . $GLOBALS['limit'] . '&start=' . $GLOBALS['start'] . '&order=' . $GLOBALS['order'] . '&sort=' . $GLOBALS['sort'] . '&filter=' . $GLOBALS['filter'], 10, _AM_SONGLIST_MSG_ARTISTS_DELETED);
                    exit(0);
                }
                $artist = $artistsHandler->get(Request::getInt('id', 0, 'REQUEST'));
                xoops_confirm(
                    ['id' => $_REQUEST['id'], 'op' => $_REQUEST['op'], 'fct' => $_REQUEST['fct'], 'limit' => $_REQUEST['limit'], 'start' => $_REQUEST['start'], 'order' => $_REQUEST['order'], 'sort' => $_REQUEST['sort'], 'filter' => $_REQUEST['filter']],
                    $_SERVER['SCRIPT_NAME'],
                    sprintf(_AM_SONGLIST_MSG_ARTISTS_DELETE, $artist->getVar('name'))
                );

                break;
        }
        break;
}

xoops_cp_footer();
