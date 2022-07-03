<?php declare(strict_types=1);

use XoopsModules\Songlist\Form\SelectCategoryForm;
use XoopsModules\Songlist\Form\SelectGenreForm;
use XoopsModules\Songlist\Form\SelectVoiceForm;
use XoopsModules\Songlist\Helper;
use XoopsModules\Songlist\AlbumsHandler;
use XoopsModules\Songlist\ArtistsHandler;
use XoopsModules\Songlist\SongsHandler;
use XoopsModules\Songlist\Utf8mapHandler;

require_once __DIR__ . '/header.php';

global $file, $op, $fct, $id, $value, $gid, $cid, $start, $limit, $singer;

$category_element = new SelectCategoryForm('', 'cid', ($_GET['cid'] ?? $cid));
$genre_element    = new SelectGenreForm('', 'gid', $gid);
$voice_element    = new SelectVoiceForm('', 'vcid', $vcid);

/** @var AlbumsHandler $albumsHandler */
$albumsHandler  = Helper::getInstance()->getHandler('Albums');
/** @var ArtistsHandler $artistsHandler */
$artistsHandler = Helper::getInstance()->getHandler('Artists');
/** @var SongsHandler $songsHandler */
$songsHandler   = Helper::getInstance()->getHandler('Songs');
/** @var Utf8mapHandler $utf8mapHandler */
$utf8mapHandler = Helper::getInstance()->getHandler('Utf8map');

switch ($op) {
    default:
    case 'search':
        $url = $songsHandler->getSearchURL();
        if (!mb_strpos($url, $_SERVER['REQUEST_URI'])) {
            header('HTTP/1.1 301 Moved Permanently');
            header('Location: ' . $url);
            exit(0);
        }

        switch ($fct) {
            default:
            case 'titleandlyrics':
                $criteria = new \CriteriaCompo();
                foreach (explode(' ', $value) as $keyword) {
                    $criteria->add(new \Criteria('title', '%' . $utf8mapHandler->convert($keyword) . '%', 'LIKE'));
                    $criteria->add(new \Criteria('lyrics', '%' . $utf8mapHandler->convert($keyword) . '%', 'LIKE'));
                }
                break;
            case 'albums':
                $criteria = new \CriteriaCompo();
                foreach (explode(' ', $value) as $keyword) {
                    $criteria->add(new \Criteria('title', '%' . $utf8mapHandler->convert($keyword) . '%', 'LIKE'));
                }
                $albums   = $albumsHandler->getObjects($criteria, true);
                $criteria = new \CriteriaCompo();
                foreach ($albums as $abid => $album) {
                    $criteria->add(new \Criteria('abid', $abid), 'OR');
                }
                break;
            case 'artists':
                $criteria = new \CriteriaCompo();
                foreach (explode(' ', $value) as $keyword) {
                    $criteria->add(new \Criteria('name', '%' . $utf8mapHandler->convert($keyword) . '%', 'LIKE'));
                }
                $artists  = $artistsHandler->getObjects($criteria, true);
                $criteria = new \CriteriaCompo();
                if (is_array($artists)) {
                    foreach ($artists as $aid => $artist) {
                        $criteria->add(new \Criteria('aids', '%"' . $aid . '"%', 'LIKE'), 'OR');
                    }
                }
                break;
            case 'lyrics':
                $criteria = new \CriteriaCompo();
                foreach (explode(' ', $value) as $keyword) {
                    $criteria->add(new \Criteria('lyrics', '%' . $utf8mapHandler->convert($keyword) . '%', 'LIKE'));
                }
                break;
            case 'title':
                $criteria = new \CriteriaCompo();
                foreach (explode(' ', $value) as $keyword) {
                    $criteria->add(new \Criteria('title', '%' . $utf8mapHandler->convert($keyword) . '%', 'LIKE'));
                }
                break;
        }

        if (0 != $gid && $GLOBALS['songlistModuleConfig']['genre']) {
            $criteria->add(new \Criteria('gids', '%"' . $gid . '"%', 'LIKE'));
        }

        if (0 != $vcid && $GLOBALS['songlistModuleConfig']['voice']) {
            $criteria->add(new \Criteria('vcid', $vcid));
        }

        if (0 != ($_GET['cid'] ?? $cid)) {
            $criteria->add(new \Criteria('cid', ($_GET['cid'] ?? $cid)));
        }

        $pagenav = new \XoopsPageNav($songsHandler->getCount($criteria), $limit, $start, 'start', "?op=$op&fct=$fct&gid=$gid&vcid=$vcid&value=$value&limit=$limit");

        $criteria->setLimit($limit);
        $criteria->setStart($start);

        $songs = $songsHandler->getObjects($criteria, false);

        $GLOBALS['xoopsOption']['template_main'] = 'songlist_search_index.tpl';
        require_once $GLOBALS['xoops']->path('/header.php');
        if ($GLOBALS['songlistModuleConfig']['force_jquery'] && !isset($GLOBALS['loaded_jquery'])) {
            $GLOBALS['xoTheme']->addScript(XOOPS_URL . _MI_SONGLIST_JQUERY, ['type' => 'text/javascript']);
            $GLOBALS['loaded_jquery'] = true;
        }
        $GLOBALS['xoTheme']->addStylesheet(XOOPS_URL . _MI_SONGLIST_STYLESHEET, ['type' => 'text/css']);
        $GLOBALS['xoopsTpl']->assign('xoConfig', $GLOBALS['songlistModuleConfig']);
        $GLOBALS['xoopsTpl']->assign('php_self', $_SERVER['SCRIPT_NAME']);
        foreach ($songs as $song) {
            $GLOBALS['xoopsTpl']->append('results', $song->toArray(true));
        }
        $GLOBALS['xoopsTpl']->assign('songs', true);
        $GLOBALS['xoopsTpl']->assign('pagenav', $pagenav->renderNav());
        $GLOBALS['xoopsTpl']->assign('category_element', $category_element->render());
        $GLOBALS['xoopsTpl']->assign('genre_element', $genre_element->render());
        $GLOBALS['xoopsTpl']->assign('voice_element', $voice_element->render());
        $GLOBALS['xoopsTpl']->assign('cid', $_SESSION['cid']);
        $GLOBALS['xoopsTpl']->assign('uri', $_SERVER['REQUEST_URI']);
        require_once $GLOBALS['xoops']->path('/footer.php');
        break;
    case 'category':
        switch ($fct) {
            default:
            case 'set':
                $_SESSION['cid'] = $id;
                break;
            case 'home':
                unset($_SESSION['cid']);
                break;
        }
        redirect_header($_SERVER['SCRIPT_NAME'] . "?op=item&fct=list&id=$id&value=$value&start=$start&limit=$limit", 10, _MD_SONGLIST_MSG_CATEGORYCHANGED);
}
