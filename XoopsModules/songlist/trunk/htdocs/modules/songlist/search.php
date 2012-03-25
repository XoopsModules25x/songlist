<?php

	include (dirname(__FILE__).'/header.php');
	
	global $file, $op, $fct, $id, $value, $gid, $cid, $singer, $start, $limit;
	
	$category_element = new SonglistFormSelectCategory('', 'cid', (isset($_GET['cid'])?($_GET['cid']):$cid));
	$genre_element = new SonglistFormSelectGenre('', 'gid', $gid);
	$singer_element = new SonglistFormSelectSinger('', 'singer', $singer);
			
	$songs_handler = xoops_getmodulehandler('songs', 'songlist');
	$artists_handler = xoops_getmodulehandler('artists', 'songlist');
	$utf8map_handler = xoops_getmodulehandler('utf8map', 'songlist');
	
	switch ($op) {
		default:
		case "search":
			
			$url = $songs_handler->getSearchURL();
			if (!strpos($url, $_SERVER['REQUEST_URI'])) {
				header( "HTTP/1.1 301 Moved Permanently" ); 
				header('Location: '.$url);
				exit(0);
			}
			
			switch ($fct) {
			default:
			case "titleandlyrics":
				$criteria = new CriteriaCompo();
				foreach(explode(' ' , $value) as $keyword) {
					$criteria->add(new Criteria('`title`', '%'.$utf8map_handler->convert($keyword).'%', 'LIKE'));
					$criteria->add(new Criteria('`lyrics`', '%'.$utf8map_handler->convert($keyword).'%', 'LIKE'));
				}
				break;
			case "lyrics":
				$criteria = new CriteriaCompo();
				foreach(explode(' ' , $value) as $keyword) {
					$criteria->add(new Criteria('`lyrics`', '%'.$utf8map_handler->convert($keyword).'%', 'LIKE'));
				}
				break;
			case "title":
				$criteria = new CriteriaCompo();
				foreach(explode(' ' , $value) as $keyword) {
					$criteria->add(new Criteria('`title`', '%'.$utf8map_handler->convert($keyword).'%', 'LIKE'));
				}
				break;
			}
			
			if ($gid != 0 && $GLOBALS['songlistModuleConfig']['genre']) {
				$criteria->add(new Criteria('`gid`', $gid));
			}
			
			if ($singer != 0 && $GLOBALS['songlistModuleConfig']['singer']) {
				$criteria->add(new Criteria('`sid`', '('.implode(',', $artists_handler->getSIDs(new Criteria('`singer`', $singer))).')', 'IN'));
			}

			if ($cid != 0) {
				$criteria->add(new Criteria('`cid`',  (isset($_GET['cid'])?($_GET['cid']):$cid)));
			}
			
			$pagenav = new XoopsPageNav($songs_handler->getCount($criteria), $limit, $start, 'start', "?op=$op&fct=$fct&gid=$gid&singer=$singer&value=$value&limit=$limit");

			$criteria->setLimit($limit);
			$criteria->setStart($start);
			
			$songs = $songs_handler->getObjects($criteria, false);					
			
			$xoopsOption['template_main'] = 'songlist_search_index.html';
			include($GLOBALS['xoops']->path('/header.php'));
			if ($GLOBALS['songlistModuleConfig']['force_jquery']&&!isset($GLOBALS['loaded_jquery'])) {
				$GLOBALS['xoTheme']->addScript(XOOPS_URL._MI_SONGLIST_JQUERY, array('type'=>'text/javascript'));
				$GLOBALS['loaded_jquery']=true;
			}
			$GLOBALS['xoTheme']->addStylesheet(XOOPS_URL._MI_SONGLIST_STYLESHEET, array('type'=>'text/css'));
			$GLOBALS['xoopsTpl']->assign('xoConfig', $GLOBALS['songlistModuleConfig']);			
			$GLOBALS['xoopsTpl']->assign('php_self', $_SERVER['PHP_SELF']);
			foreach($songs as $song) {
				$GLOBALS['xoopsTpl']->append('results', $song->toArray(true));
			}
			$GLOBALS['xoopsTpl']->assign('songs', true);			
			$GLOBALS['xoopsTpl']->assign('pagenav', $pagenav->renderNav());
			$GLOBALS['xoopsTpl']->assign('category_element', $category_element->render());
			$GLOBALS['xoopsTpl']->assign('genre_element', $genre_element->render());
			$GLOBALS['xoopsTpl']->assign('singer_element', $singer_element->render());				
			$GLOBALS['xoopsTpl']->assign('cid', $_SESSION['cid']);
			$GLOBALS['xoopsTpl']->assign('uri', $_SERVER['REQUEST_URI']);	
			include($GLOBALS['xoops']->path('/footer.php'));
			break;

			
		case "category":
			switch ($fct) {
			default:
			case "set":
				$_SESSION['cid'] = $id;
				break;
			case "home":
				unset($_SESSION['cid']);
				break;
			}
			redirect_header($_SERVER["PHP_SELF"]."?op=item&fct=list&id=$id&value=$value&start=$start&limit=$limit", 10, _MN_SONGLIST_MSG_CATEGORYCHANGED);
			exit;
	}	
?>