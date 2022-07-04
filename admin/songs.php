<?php declare(strict_types=1);

use Xmf\Module\Admin;
use Xmf\Request;
use XoopsModules\Songlist\{
    Form\FormController,
    Helper,
    Songs,
    SongsHandler,
    Uploader
};

/** @var Songs $songs */

require __DIR__ . '/header.php';

xoops_loadLanguage('admin', 'songlist');

xoops_cp_header();

$op     = $_REQUEST['op'] ?? 'songs';
$fct    = $_REQUEST['fct'] ?? 'list';
$limit  = Request::getInt('limit', 30, 'REQUEST');
$start  = Request::getInt('start', 0, 'REQUEST');
$order  = !empty($_REQUEST['order']) ? $_REQUEST['order'] : 'DESC';
$sort   = !empty($_REQUEST['sort']) ? '' . $_REQUEST['sort'] . '' : 'created';
$filter = !empty($_REQUEST['filter']) ? '' . $_REQUEST['filter'] . '' : '1,1';

switch ($op) {
    default:
    case 'songs':
        switch ($fct) {
            default:
            case 'list':
                $adminObject = Admin::getInstance();
                $adminObject->displayNavigation(basename(__FILE__));

            /** @var SongsHandler $songsHandler */
                $songsHandler = Helper::getInstance()->getHandler('Songs');

                $criteria        = $songsHandler->getFilterCriteria($GLOBALS['filter']);
                $ttl             = $songsHandler->getCount($criteria);
                $GLOBALS['sort'] = !empty($_REQUEST['sort']) ? '' . $_REQUEST['sort'] . '' : 'created';

                $pagenav = new \XoopsPageNav($ttl, $GLOBALS['limit'], $GLOBALS['start'], 'start', 'limit=' . $GLOBALS['limit'] . '&sort=' . $GLOBALS['sort'] . '&order=' . $GLOBALS['order'] . '&op=' . $GLOBALS['op'] . '&fct=' . $GLOBALS['fct'] . '&filter=' . $GLOBALS['filter']);
                $GLOBALS['xoopsTpl']->assign('pagenav', $pagenav->renderNav());

                foreach ($songsHandler->filterFields() as $id => $key) {
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
                    $GLOBALS['xoopsTpl']->assign('filter_' . \mb_strtolower(str_replace('-', '_', $key)) . '_th', $songsHandler->getFilterForm($GLOBALS['filter'], $key, $GLOBALS['sort'], $GLOBALS['op'], $GLOBALS['fct']));
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

                $songss = $songsHandler->getObjects($criteria, true);
                foreach ($songss as $cid => $songs) {
                    if (is_object($songs)) {
                        $GLOBALS['xoopsTpl']->append('songs', $songs->toArray());
                    }
                }
                $GLOBALS['xoopsTpl']->assign('form', FormController::getFormSongs(false));
                $GLOBALS['xoopsTpl']->assign('php_self', $_SERVER['SCRIPT_NAME']);
                $GLOBALS['xoopsTpl']->display('db:songlist_cpanel_songs_list.tpl');
                break;
            case 'new':
            case 'edit':
                $adminObject = Admin::getInstance();
                $adminObject->displayNavigation(basename(__FILE__));

                require_once $GLOBALS['xoops']->path('/class/pagenav.php');

                $songsHandler = Helper::getInstance()->getHandler('Songs');
                if (Request::hasVar('id', 'REQUEST')) {
                    $songs = $songsHandler->get(Request::getInt('id', 0, 'REQUEST'));
                } else {
                    $songs = $songsHandler->create();
                }

                $GLOBALS['xoopsTpl']->assign('form', $songs->getForm());
                $GLOBALS['xoopsTpl']->assign('php_self', $_SERVER['SCRIPT_NAME']);
                $GLOBALS['xoopsTpl']->display('db:songlist_cpanel_songs_edit.tpl');
                break;
            case 'save':
                $songsHandler  = Helper::getInstance()->getHandler('Songs');
                $extrasHandler = Helper::getInstance()->getHandler('Extras');
                $id            = 0;
                $id            = Request::getInt('id', 0, 'REQUEST');
                if ($id) {
                    $songs = $songsHandler->get($id);
                } else {
                    $songs = $songsHandler->create();
                }
                $songs->setVars($_POST[$id]);

                if (Request::hasVar('mp3' . $id, 'FILES') && !empty($_FILES['mp3' . $id]['title'])) {
//                    if (!is_dir($GLOBALS['xoops']->path($GLOBALS['songlistModuleConfig']['upload_areas']))) {
//                        foreach (explode('\\', $GLOBALS['xoops']->path($GLOBALS['songlistModuleConfig']['upload_areas'])) as $folders) {
//                            foreach (explode('/', $folders) as $folder) {
//                                $path .= DS . $folder;
//                                if (!mkdir($path, 0777) && !is_dir($path)) {
//                                    throw new \RuntimeException(sprintf('Directory "%s" was not created', $path));
//                                }
//                            }
//                        }
//                    }

//                    require_once $GLOBALS['xoops']->path('modules/songlist/include/uploader.php');
                    $uploader = new Uploader(
                        $GLOBALS['xoops']->path($GLOBALS['songlistModuleConfig']['upload_areas']),
                        explode('|', $GLOBALS['songlistModuleConfig']['mp3_mimetype']),
                        $GLOBALS['songlistModuleConfig']['mp3_filesize'],
                        0,
                        0,
                        explode('|', $GLOBALS['songlistModuleConfig']['mp3_extensions'])
                    );
                    try {
                        $uploader->setPrefix(mb_substr(md5((string)microtime(true)), random_int(0, 20), 13));
                    } catch (Exception $e) {
                    }

                    if ($uploader->fetchMedia('mp3' . $id)) {
                        if (!$uploader->upload()) {
                            $adminObject = Admin::getInstance();
                            $adminObject->displayNavigation(basename(__FILE__));
                            echo $uploader->getErrors();
                            xoops_cp_footer();
                            exit(0);
                        }
                        if (mb_strlen($songs->getVar('mp3'))) {
                            unlink($GLOBALS['xoops']->path($songs->getVar('path')) . basename($songs->getVar('mp3')));
                        }

                        $songs->setVar('mp3', XOOPS_URL . '/' . str_replace(DS, '/', $GLOBALS['songlistModuleConfig']['upload_areas']) . $uploader->getSavedFileName());
                    } else {
                        $adminObject = Admin::getInstance();
                        $adminObject->displayNavigation(basename(__FILE__));
                        echo $uploader->getErrors();
                        xoops_cp_footer();
                        exit(0);
                    }
                }
                if (!$id = $songsHandler->insert($songs)) {
                    redirect_header($_SERVER['SCRIPT_NAME'] . '?op=' . $GLOBALS['op'] . '&fct=list&limit=' . $GLOBALS['limit'] . '&start=' . $GLOBALS['start'] . '&order=' . $GLOBALS['order'] . '&sort=' . $GLOBALS['sort'] . '&filter=' . $GLOBALS['filter'], 10, _AM_SONGLIST_MSG_SONGS_FAILEDTOSAVE);
                    exit(0);
                }
                $extra = $extrasHandler->get($id);
                $extra->setVars($_POST[$id]);
                $extra->setVar('sid', $id);
                $extrasHandler->insert($extra);

                if ($GLOBALS['songlistModuleConfig']['tags'] && file_exists(XOOPS_ROOT_PATH . '/modules/tag/class/tag.php')) {
                    $tagHandler = \XoopsModules\Tag\Helper::getInstance()->getHandler('Tag');
                    $tagHandler->updateByItem($_POST['tags'], $id, $GLOBALS['songlistModule']->getVar('dirname'), $songs->getVar('cid'));
                }

                if ('new' === isset($_REQUEST['state']) ? $_REQUEST['state'][$_REQUEST['id']]:'') {
                    redirect_header(
                        $_SERVER['SCRIPT_NAME'] . '?op=' . $GLOBALS['op'] . '&fct=edit&id=' . $_REQUEST['id'] . '&limit=' . $GLOBALS['limit'] . '&start=' . $GLOBALS['start'] . '&order=' . $GLOBALS['order'] . '&sort=' . $GLOBALS['sort'] . '&filter=' . $GLOBALS['filter'],
                        10,
                        _AM_SONGLIST_MSG_SONGS_SAVEDOKEY
                    );
                } else {
                    redirect_header($_SERVER['SCRIPT_NAME'] . '?op=' . $GLOBALS['op'] . '&fct=list&limit=' . $GLOBALS['limit'] . '&start=' . $GLOBALS['start'] . '&order=' . $GLOBALS['order'] . '&sort=' . $GLOBALS['sort'] . '&filter=' . $GLOBALS['filter'], 10, _AM_SONGLIST_MSG_SONGS_SAVEDOKEY);
                }
                exit(0);

                break;
            case 'savelist':
                print_r($_FILES);
                exit;
                $songsHandler = Helper::getInstance()->getHandler('Songs');
                foreach ($_REQUEST['id'] as $id) {
                    $songs = $songsHandler->get($id);
                    $songs->setVars($_POST[$id]);
                    if (Request::hasVar('mp3' . $id, 'FILES') && !empty($_FILES['mp3' . $id]['title'])) {
//                        if (!is_dir($GLOBALS['xoops']->path($GLOBALS['songlistModuleConfig']['upload_areas']))) {
//                            foreach (explode('\\', $GLOBALS['xoops']->path($GLOBALS['songlistModuleConfig']['upload_areas'])) as $folders) {
//                                foreach (explode('/', $folders) as $folder) {
//                                    $path .= DS . $folder;
//                                    if (!mkdir($path, 0777) && !is_dir($path)) {
//                                        throw new \RuntimeException(sprintf('Directory "%s" was not created', $path));
//                                    }
//                                }
//                            }
//                        }

//                        require_once $GLOBALS['xoops']->path('modules/songlist/include/uploader.php');
                        $uploader = new Uploader(
                            $GLOBALS['xoops']->path($GLOBALS['songlistModuleConfig']['upload_areas']),
                            explode('|', $GLOBALS['songlistModuleConfig']['mp3_mimetype']),
                            $GLOBALS['songlistModuleConfig']['mp3_filesize'],
                            0,
                            0,
                            explode('|', $GLOBALS['songlistModuleConfig']['mp3_extensions'])
                        );
                        try {
                            $uploader->setPrefix(mb_substr(md5((string)microtime(true)), random_int(0, 20), 13));
                        } catch (Exception $e) {
                        }

                        if ($uploader->fetchMedia('mp3' . $id)) {
                            if (!$uploader->upload()) {
                                $adminObject = Admin::getInstance();
                                $adminObject->displayNavigation(basename(__FILE__));
                                echo $uploader->getErrors();
                                xoops_cp_footer();
                                exit(0);
                            }
                            if (mb_strlen($songs->getVar('mp3'))) {
                                unlink($GLOBALS['xoops']->path($songs->getVar('path')) . basename($songs->getVar('mp3')));
                            }

                            $songs->setVar('mp3', XOOPS_URL . '/' . str_replace(DS, '/', $GLOBALS['songlistModuleConfig']['upload_areas']) . $uploader->getSavedFileName());
                        } else {
                            $adminObject = Admin::getInstance();
                            $adminObject->displayNavigation(basename(__FILE__));
                            echo $uploader->getErrors();
                            xoops_cp_footer();
                            exit(0);
                        }
                    }
                    if (!$songsHandler->insert($songs)) {
                        redirect_header(
                            $_SERVER['SCRIPT_NAME'] . '?op=' . $GLOBALS['op'] . '&fct=list&limit=' . $GLOBALS['limit'] . '&start=' . $GLOBALS['start'] . '&order=' . $GLOBALS['order'] . '&sort=' . $GLOBALS['sort'] . '&filter=' . $GLOBALS['filter'],
                            10,
                            _AM_SONGLIST_MSG_SONGS_FAILEDTOSAVE
                        );
                        exit(0);
                    }
                }
                redirect_header($_SERVER['SCRIPT_NAME'] . '?op=' . $GLOBALS['op'] . '&fct=list&limit=' . $GLOBALS['limit'] . '&start=' . $GLOBALS['start'] . '&order=' . $GLOBALS['order'] . '&sort=' . $GLOBALS['sort'] . '&filter=' . $GLOBALS['filter'], 10, _AM_SONGLIST_MSG_SONGS_SAVEDOKEY);
                exit(0);
                break;
            case 'delete':
                $songsHandler = Helper::getInstance()->getHandler('Songs');
                $id           = 0;
                if (Request::hasVar('id', 'POST') && $id = Request::getInt('id', 0, 'POST')) {
                    $songs = $songsHandler->get($id);
                    if (!$songsHandler->delete($songs)) {
                        redirect_header(
                            $_SERVER['SCRIPT_NAME'] . '?op=' . $GLOBALS['op'] . '&fct=list&limit=' . $GLOBALS['limit'] . '&start=' . $GLOBALS['start'] . '&order=' . $GLOBALS['order'] . '&sort=' . $GLOBALS['sort'] . '&filter=' . $GLOBALS['filter'],
                            10,
                            _AM_SONGLIST_MSG_SONGS_FAILEDTODELETE
                        );
                        exit(0);
                    }
                    redirect_header($_SERVER['SCRIPT_NAME'] . '?op=' . $GLOBALS['op'] . '&fct=list&limit=' . $GLOBALS['limit'] . '&start=' . $GLOBALS['start'] . '&order=' . $GLOBALS['order'] . '&sort=' . $GLOBALS['sort'] . '&filter=' . $GLOBALS['filter'], 10, _AM_SONGLIST_MSG_SONGS_DELETED);
                    exit(0);
                }
                $songs = $songsHandler->get(Request::getInt('id', 0, 'REQUEST'));
                xoops_confirm(
                    ['id' => $_REQUEST['id'], 'op' => $_REQUEST['op'], 'fct' => $_REQUEST['fct'], 'limit' => $_REQUEST['limit'], 'start' => $_REQUEST['start'], 'order' => $_REQUEST['order'], 'sort' => $_REQUEST['sort'], 'filter' => $_REQUEST['filter']],
                    $_SERVER['SCRIPT_NAME'],
                    sprintf(_AM_SONGLIST_MSG_SONGS_DELETE, $songs->getVar('name'))
                );

                break;
        }
        break;
}

xoops_cp_footer();
