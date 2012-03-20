<?php

if (!defined('XOOPS_ROOT_PATH')) {
	exit();
}

class SonglistSongs extends XoopsObject
{

    function SonglistSongs($fid = null)
    {
        $this->initVar('sid', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('cid', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('gid', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('aids', XOBJ_DTYPE_ARRAY, array(), false);
        $this->initVar('abid', XOBJ_DTYPE_INT, 0, false);
		$this->initVar('songid', XOBJ_DTYPE_TXTBOX, null, false, 32);
		$this->initVar('title', XOBJ_DTYPE_TXTBOX, null, false, 128);
		$this->initVar('lyrics', XOBJ_DTYPE_OTHER, null, false);		
		$this->initVar('hits', XOBJ_DTYPE_INT, 0, false);
		$this->initVar('rank', XOBJ_DTYPE_DECIMAL, 0, false);
		$this->initVar('votes', XOBJ_DTYPE_INT, 0, false);
		$this->initVar('tags', XOBJ_DTYPE_TXTBOX, null, false, 255);
		$this->initVar('created', XOBJ_DTYPE_INT, 0, false);
		$this->initVar('updated', XOBJ_DTYPE_INT, 0, false);
	}
	
    function getForm($as_array = false) {
		return songlist_songs_get_form($this, $as_array);
	}

	function toArray() {
		$ret = parent::toArray();
		$form = $this->getForm(true);
		foreach($form as $key => $element) {
			$ret['form'][$key] = $form[$key]->render();	
		}
		foreach(array('created', 'updated') as $key) {
			if ($this->getVar($key)>0) {
				$ret['form'][$key] = date(_DATESTRING, $this->getVar($key)); 
				$ret[$key] = date(_DATESTRING, $this->getVar($key));
			}
		}

		$ret['rank'] = number_format($this->getVar('rank')/$this->getVar('votes'),2)._MI_SONGLIST_OFTEN;
    		
		return $ret;
	}
}


class SonglistSongsHandler extends XoopsPersistableObjectHandler
{
    function __construct(&$db) 
    {
        parent::__construct($db, "songlist_songs", 'SonglistSongs', "sid", "title");
    }

	function filterFields() {
		return array('sid', 'cid', 'aids', 'abid', 'songid', 'title', 'lyrics', 'hits', 'rank', 'votes', 'tags', 'created', 'updated');
	}
	
    function getFilterCriteria($filter) {
    	$parts = explode('|', $filter);
    	$criteria = new CriteriaCompo();
    	foreach($parts as $part) {
    		$var = explode(',', $part);
    		if (!empty($var[1])&&!is_numeric($var[0])) {
    			$object = $this->create();
    			if (		$object->vars[$var[0]]['data_type']==XOBJ_DTYPE_TXTBOX || 
    						$object->vars[$var[0]]['data_type']==XOBJ_DTYPE_TXTAREA) 	{
    				$criteria->add(new Criteria('`'.$var[0].'`', '%'.$var[1].'%', (isset($var[2])?$var[2]:'LIKE')));
    			} elseif (	$object->vars[$var[0]]['data_type']==XOBJ_DTYPE_INT || 
    						$object->vars[$var[0]]['data_type']==XOBJ_DTYPE_DECIMAL || 
    						$object->vars[$var[0]]['data_type']==XOBJ_DTYPE_FLOAT ) 	{
    				$criteria->add(new Criteria('`'.$var[0].'`', $var[1], (isset($var[2])?$var[2]:'=')));			
				} elseif (	$object->vars[$var[0]]['data_type']==XOBJ_DTYPE_ENUM ) 	{
    				$criteria->add(new Criteria('`'.$var[0].'`', $var[1], (isset($var[2])?$var[2]:'=')));    				
				} elseif (	$object->vars[$var[0]]['data_type']==XOBJ_DTYPE_ARRAY ) 	{
    				$criteria->add(new Criteria('`'.$var[0].'`', '%"'.$var[1].'";%', (isset($var[2])?$var[2]:'LIKE')));    				
				}
    		} elseif (!empty($var[1])&&is_numeric($var[0])) {
    			$criteria->add(new Criteria($var[0], $var[1]));
    		}
    	}
    	return $criteria;
    }
        
    function getFilterForm($filter, $field, $sort='created', $op = 'dashboard', $fct='list') {
    	$ele = songlist_getFilterElement($filter, $field, $sort, $op, $fct);
    	if (is_object($ele))
    		return $ele->render();
    	else 
    		return '&nbsp;';
    }
    
	function insert($obj, $force=true, $object = null) {
    	if ($obj->isNew()) {
    		$new = true;
    		$old = $this->create();
    		$obj->setVar('created', time());	
    	} else {
    		$new = false;
    		$old = $this->get($obj->getVar('sid'));
    		$obj->setVar('updated', time());
    	}
		
    	$albums_handler = xoops_getmodulehandler('albums', 'songlist');
		$artists_handler = xoops_getmodulehandler('artists', 'songlist');
		$genre_handler = xoops_getmodulehandler('genre', 'songlist');
		$category_handler = xoops_getmodulehandler('category', 'songlist');

		if ($obj->vars['gid']['changed']==true) {
    		if ($new==true||($old->getVar('gid')!=$obj->vars['gid']['value']&&$obj->vars['gid']['value']!=0)) {
    			$genre = $genre_handler->get($obj->vars['gid']['value']);
    			$genre->setVar('songs', $genre->getVar('songs')+1);
    			$genre_handler->insert($genre, true, $obj);
    		} 
    		if ($old->getVar('gid')>0&&$old->getVar('gid')!=$obj->vars['gid']['value']) {
    			$genre = $genre_handler->get($old->vars['gid']['value']);
    			$genre->setVar('songs', $genre->getVar('songs')-1);
    			$genre_handler->insert($genre, true, null);
    		}
    	}
    	
		if ($obj->vars['cid']['changed']==true) {
    		if ($new==true||($old->getVar('cid')!=$obj->vars['cid']['value']&&$obj->vars['cid']['value']!=0)) {
    			$category = $category_handler->get($obj->vars['cid']['value']);
    			$category->setVar('songs', $category->getVar('songs')+1);
    			$category_handler->insert($category, true, $obj);
    		}
			if ($old->getVar('cid')>0&&$old->getVar('cid')!=$obj->vars['cid']['value']) {
				$category = $category_handler->get($old->vars['cid']['value']);
    			$category->setVar('songs', $category->getVar('songs')-1);
    			$category_handler->insert($category, true, null);
    		}
    	}
    	
    	if ($obj->vars['aids']['changed']==true&&count($obj->vars['aids']['value'])!=0) {
    		if ($new==true||$old->getVar('aids')!=$obj->vars['aids']['value']) {
    			foreach($obj->vars['aids']['value'] as $aid) {
    				if (!in_array($aid, $old->vars['aids']['value'])) {
		    			$artists = $artists_handler->get($aid);
		    			$artists->setVar('songs', $artists->getVar('songs')+1);
		    			$artists_handler->insert($artists, true, $obj);
    				}
    			}
    		}
    		if (count($old->getVar('aids'))==0) {
    			foreach($old->getVar('aids') as $aid) {
    				if (!in_array($aid, $obj->vars['aids']['value'])) {
	    				$artists = $artists_handler->get($aid);
		    			$artists->setVar('songs', $artists->getVar('songs')-1);
		    			$artists_handler->insert($artists, true, null);	
    				}
    			}
    		}
    	}
    	
    	if ($obj->vars['abid']['changed']==true) {
    		if ($new==true||($old->getVar('abid')!=$obj->vars['abid']['value']&&$obj->vars['abid']['value']!=0)) {
    			$album = $albums_handler->get($obj->vars['abid']['value']);
    			$album->setVar('songs', $album->getVar('songs')+1);
    			$albums_handler->insert($album, true, $obj);
    		}
    		if ($old->getVar('abid')>0&&$old->getVar('abid')!=$obj->vars['abid']['value']) {
    			$album = $albums_handler->get($obj->vars['abid']['value']);
    			$album->setVar('songs', $album->getVar('songs')-1);
    			$albums_handler->insert($album, true, null);
    		}
    	}
    	
    	$sid = parent::insert($obj, $force);
    	if ($obj->vars['abid']['value']>0) {
    		$album = $albums_handler->get($obj->vars['abid']['value']);
    		$arry = $album->getVar('sids');
    		$arry[$sid] = $sid;
    		$album->setVar('sids', $arry);
    		$albums_handler->insert($album);
    	}
		if ($obj->vars['aid']['value']>0) {
    		$artist = $artists_handler->get($obj->vars['aid']['value']);
    		$arry = $artist->getVar('sids');
    		$arry[$sid] = $sid;
    		$artist->setVar('sids', $arry);
    		$artists_handler->insert($artist);
    	}
    	return $sid;
    }
     
}
?>