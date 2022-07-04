<?php declare(strict_types=1);

use Xmf\Module\Admin;
use Xmf\Request;
use XoopsModules\Songlist\Helper;
use XoopsModules\Songlist\Uploader;
use XoopsModules\Songlist\AlbumsHandler;
use XoopsModules\Songlist\Form\FormController;

require __DIR__ . '/header.php';

xoops_loadLanguage('admin', 'songlist');

xoops_cp_header();

$op     = Request::getString('op', 'index', 'REQUEST');
$fct    = Request::getString('fct', '', 'REQUEST');
$limit  = Request::getInt('limit', 30, 'REQUEST');
$start  = Request::getInt('start', 0, 'REQUEST');
$order  = Request::getString('order', 'DESC', 'REQUEST');
$sort   = Request::getString('sort', 'created', 'REQUEST');
$filter = Request::getString('filter', '1,1', 'REQUEST');

$albumsHandler   = Helper::getInstance()->getHandler('Albums');
$songsHandler    = Helper::getInstance()->getHandler('Songs');
$artistsHandler  = Helper::getInstance()->getHandler('Artists');
$genreHandler    = Helper::getInstance()->getHandler('Genre');
$voiceHandler    = Helper::getInstance()->getHandler('Voice');
$categoryHandler = Helper::getInstance()->getHandler('Category');

switch ($op) {
    case 'import':
        switch ($fct) {
            default:
            case 'actiona':
                if (Request::hasVar('xmlfile', 'SESSION')) {
                    redirect_header($_SERVER['SCRIPT_NAME'] . '?file=' . $_SESSION['xmlfile'] . '&op=import&fct=actionb', 10, _AM_SONGLIST_XMLFILE_UPLOADED);
                }
                $adminObject = Admin::getInstance();
                $adminObject->displayNavigation(basename(__FILE__));

                $GLOBALS['xoopsTpl']->assign('form', FormController::getFormImport(false));
                $GLOBALS['xoopsTpl']->assign('php_self', $_SERVER['SCRIPT_NAME']);
                $GLOBALS['xoopsTpl']->display('db:songlist_cpanel_import_actiona.tpl');
                break;
            case 'upload':
                if (Request::hasVar('xmlfile', 'FILES') && !empty($_FILES['xmlfile']['title'])) {
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
                    $albums   = $albumsHandler->get($id);
                    $uploader = new Uploader(
                        $GLOBALS['xoops']->path($GLOBALS['songlistModuleConfig']['upload_areas']),
                        ['application/xml', 'application/xml-dtd', 'application/xml-external-parsed-entity', 'text/xml xml xsl', 'text/xml-external-parsed-entity'],
                        1024 * 1024 * 32,
                        0,
                        0,
                        ['xml']
                    );
                    try {
                        $uploader->setPrefix(mb_substr(md5((string)microtime(true)), random_int(0, 20), 13));
                    } catch (Exception $e) {
                    }

                    if ($uploader->fetchMedia('xmlfile')) {
                        if (!$uploader->upload()) {
                            echo $uploader->getErrors();
                            require __DIR__ . '/admin_footer.php';
                            exit(0);
                        }
                        $_SESSION['xmlfile'] = $uploader->getSavedFileName();
                        redirect_header($_SERVER['SCRIPT_NAME'] . '?file=' . $uploader->getSavedFileName() . '&op=import&fct=actionb', 10, _AM_SONGLIST_XMLFILE_UPLOADED);
                    } else {
                        echo $uploader->getErrors();
                        require __DIR__ . '/admin_footer.php';
                        exit(0);
                    }
                }
                break;
            case 'actionb':
                $adminObject = Admin::getInstance();
                $adminObject->displayNavigation(basename(__FILE__));

                $GLOBALS['xoopsTpl']->assign('form', FormController::getFormImportb($_SESSION['xmlfile']));
                $GLOBALS['xoopsTpl']->assign('php_self', $_SERVER['SCRIPT_NAME']);
                $GLOBALS['xoopsTpl']->display('db:songlist_cpanel_import_actionb.tpl');
                break;
            case 'import':

                $filesize = filesize($GLOBALS['xoops']->path($GLOBALS['songlistModuleConfig']['upload_areas'] . $_SESSION['xmlfile']));
                $mb       = floor($filesize / 1024 / 1024);
                if ($mb > 32) {
                    ini_set('memory_limit', ($mb + 128) . 'M');
                }
                set_time_limit(3600);

                $xmlarray = Utility::xml2array(file_get_contents($GLOBALS['xoops']->path($GLOBALS['songlistModuleConfig']['upload_areas'] . $_SESSION['xmlfile'])), false, 'tag');

                if (mb_strlen($_POST['collection']) > 0) {
                    foreach ($xmlarray[$_POST['collection']] as $id => $record) {
                        foreach ($record as $recid => $data) {
                            $gid = 0;
                            if (mb_strlen($_POST['genre']) > 0 && !empty($data[$_POST['genre']])) {
                                $criteria = new \Criteria('name', $data[$_POST['genre']]);
                                if ($genreHandler->getCount($criteria) > 0) {
                                    $objects = $genreHandler->getObjects($criteria, false);
                                    $gid     = $objects[0]->getVar('gid');
                                } else {
                                    $object = $genreHandler->create();
                                    $object->setVar('name', $data[$_POST['genre']]);
                                    $gid = $genreHandler->insert($object);
                                }
                            }

                            $vid = 0;
                            if (mb_strlen($_POST['voice']) > 0 && !empty($data[$_POST['voice']])) {
                                $criteria = new \Criteria('name', $data[$_POST['voice']]);
                                if ($voiceHandler->getCount($criteria) > 0) {
                                    $objects = $voiceHandler->getObjects($criteria, false);
                                    $gid     = $objects[0]->getVar('vid');
                                } else {
                                    $object = $voiceHandler->create();
                                    $object->setVar('name', $data[$_POST['voice']]);
                                    $gid = $voiceHandler->insert($object);
                                }
                            }

                            $cid = 0;
                            if (mb_strlen($_POST['category']) > 0 && !empty($data[$_POST['category']])) {
                                $criteria = new \Criteria('name', $data[$_POST['category']]);
                                if ($categoryHandler->getCount($criteria) > 0) {
                                    $objects = $categoryHandler->getObjects($criteria, false);
                                    $cid     = $objects[0]->getVar('cid');
                                } else {
                                    $object = $categoryHandler->create();
                                    $object->setVar('name', $data[$_POST['category']]);
                                    $cid = $categoryHandler->insert($object);
                                }
                            }
                            $aids = [];
                            if (mb_strlen($_POST['artist']) > 0 && !empty($data[$_POST['artist']])) {
                                foreach (explode(',', $data[$_POST['artist']]) as $artist) {
                                    $criteria = new \Criteria('name', $artist);
                                    if ($artistsHandler->getCount($criteria) > 0) {
                                        $objects                          = $artistsHandler->getObjects($criteria, false);
                                        $aids[$objects[0]->getVar('aid')] = $objects[0]->getVar('aid');
                                    } else {
                                        $object = $artistsHandler->create();
                                        $object->setVar('cid', $cid);
                                        switch ($data[$_POST['singer']]) {
                                            case $_POST['duet']:
                                                $object->setVar('singer', '_ENUM_SONGLIST_DUET');
                                                break;
                                            case $_POST['solo']:
                                                $object->setVar('singer', '_ENUM_SONGLIST_SOLO');
                                                break;
                                        }
                                        $object->setVar('name', $data[$_POST['artist']]);
                                        $aid        = $artistsHandler->insert($object);
                                        $aids[$aid] = $aid;
                                    }
                                }
                            }
                            $abid = 0;
                            if (mb_strlen($_POST['album']) > 0 && !empty($data[$_POST['album']])) {
                                $criteria = new \Criteria('name', $data[$_POST['album']]);
                                if ($albumsHandler->getCount($criteria) > 0) {
                                    $objects = $albumsHandler->getObjects($criteria, false);
                                    $abid    = $objects[0]->getVar('aid');
                                } else {
                                    $object = $albumsHandler->create();
                                    $object->setVar('cid', $cid);
                                    $object->setVar('aids', $aids);
                                    $object->setVar('name', $data[$_POST['album']]);
                                    $abid = $albumsHandler->insert($object);
                                }
                            }
                            $sid = 0;
                            if (mb_strlen($_POST['songid']) > 0 && !empty($data[$_POST['songid']])) {
                                $criteria = new \Criteria('songid', $data[$_POST['songid']]);
                                if ($songsHandler->getCount($criteria) > 0) {
                                    $objects = $songsHandler->getObjects($criteria, false);
                                    $object  = $objects[0]->getVar('sid');
                                } else {
                                    $object = $songsHandler->create();
                                }
                                $object->setVar('cid', $cid);
                                $object->setVar('gid', $gid);
                                $object->setVar('aids', $aids);
                                $object->setVar('abid', $abid);
                                $object->setVar('songid', $data[$_POST['songid']]);
                                $object->setVar('title', $data[$_POST['title']]);
                                $object->setVar('lyrics', str_replace("\n", "<br>\n", $data[$_POST['lyrics']]));
                                $sid = $songsHandler->insert($object);
                            }
                        }
                    }
                } else {
                    foreach ($xmlarray as $recid => $data) {
                        $gid = 0;
                        if (mb_strlen($_POST['genre']) > 0 && !empty($data[$_POST['genre']])) {
                            $criteria = new \Criteria('name', $data[$_POST['genre']]);
                            if ($genreHandler->getCount($criteria) > 0) {
                                $objects = $genreHandler->getObjects($criteria, false);
                                $gid     = $objects[0]->getVar('gid');
                            } else {
                                $object = $genreHandler->create();
                                $object->setVar('name', $data[$_POST['genre']]);
                                $gid = $genreHandler->insert($object);
                            }
                        }
                        $vid = 0;
                        if (mb_strlen($_POST['voice']) > 0 && !empty($data[$_POST['voice']])) {
                            $criteria = new \Criteria('name', $data[$_POST['voice']]);
                            if ($voiceHandler->getCount($criteria) > 0) {
                                $objects = $voiceHandler->getObjects($criteria, false);
                                $gid     = $objects[0]->getVar('vid');
                            } else {
                                $object = $voiceHandler->create();
                                $object->setVar('name', $data[$_POST['voice']]);
                                $gid = $voiceHandler->insert($object);
                            }
                        }
                        $cid = 0;
                        if (mb_strlen($_POST['category']) > 0 && !empty($data[$_POST['category']])) {
                            $criteria = new \Criteria('name', $data[$_POST['category']]);
                            if ($categoryHandler->getCount($criteria) > 0) {
                                $objects = $categoryHandler->getObjects($criteria, false);
                                $cid     = $objects[0]->getVar('cid');
                            } else {
                                $object = $categoryHandler->create();
                                $object->setVar('name', $data[$_POST['category']]);
                                $cid = $categoryHandler->insert($object);
                            }
                        }
                        $aids = [];
                        if (mb_strlen($_POST['artist']) > 0 && !empty($data[$_POST['artist']])) {
                            foreach (explode(',', $data[$_POST['artist']]) as $artist) {
                                $criteria = new \Criteria('name', $artist);
                                if ($artistsHandler->getCount($criteria) > 0) {
                                    $objects                          = $artistsHandler->getObjects($criteria, false);
                                    $aids[$objects[0]->getVar('aid')] = $objects[0]->getVar('aid');
                                } else {
                                    $object = $artistsHandler->create();
                                    switch ($data[$_POST['singer']]) {
                                        case $_POST['duet']:
                                            $object->setVar('singer', '_ENUM_SONGLIST_DUET');
                                            break;
                                        case $_POST['solo']:
                                            $object->setVar('singer', '_ENUM_SONGLIST_SOLO');
                                            break;
                                    }
                                    $object->setVar('cid', $cid);
                                    $object->setVar('name', $data[$_POST['artist']]);
                                    $aid        = $artistsHandler->insert($object);
                                    $aids[$aid] = $aid;
                                }
                            }
                        }
                        $abid = 0;
                        if (mb_strlen($_POST['album']) > 0 && !empty($data[$_POST['album']])) {
                            $criteria = new \Criteria('name', $data[$_POST['album']]);
                            if ($albumsHandler->getCount($criteria) > 0) {
                                $objects = $albumsHandler->getObjects($criteria, false);
                                $abid    = $objects[0]->getVar('aid');
                            } else {
                                $object = $albumsHandler->create();
                                $object->setVar('cid', $cid);
                                $object->setVar('aids', $aids);
                                $object->setVar('name', $data[$_POST['album']]);
                                $abid = $albumsHandler->insert($object);
                            }
                        }
                        $sid = 0;
                        if (mb_strlen($_POST['songid']) > 0 && !empty($data[$_POST['songid']])) {
                            $criteria = new \Criteria('songid', $data[$_POST['songid']]);
                            if ($songsHandler->getCount($criteria) > 0) {
                                $objects = $songsHandler->getObjects($criteria, false);
                                $object  = $objects[0]->getVar('sid');
                            } else {
                                $object = $songsHandler->create();
                            }
                            $object->setVar('cid', $cid);
                            $object->setVar('gid', $gid);
                            $object->setVar('aids', $aids);
                            $object->setVar('abid', $abid);
                            $object->setVar('songid', $data[$_POST['songid']]);
                            $object->setVar('title', $data[$_POST['title']]);
                            $object->setVar('lyrics', str_replace("\n", "<br>\n", $data[$_POST['lyrics']]));
                            $sid = $songsHandler->insert($object);
                        }
                    }
                }
                unlink($GLOBALS['xoops']->path($GLOBALS['songlistModuleConfig']['upload_areas'] . $_SESSION['xmlfile']));
                unset($_SESSION['xmlfile']);
                redirect_header($_SERVER['SCRIPT_NAME'] . '&op=import&fct=actiona', 10, _AM_SONGLIST_XMLFILE_COMPLETE);
                break;
        }
        break;
}

xoops_cp_footer();
