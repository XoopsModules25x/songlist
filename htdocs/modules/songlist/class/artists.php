<?php

if (!defined('XOOPS_ROOT_PATH')) {
	exit();
}

class SonglistArtists extends XoopsObject
{

    function SonglistArtists($fid = null)
    {
        $this->initVar('aid', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('cids', XOBJ_DTYPE_ARRAY, array(), false);
        $this->initVar('sids', XOBJ_DTYPE_ARRAY, array(), false);
        $this->initVar('singer', XOBJ_DTYPE_ENUM, null, false, false, false, array('_ENUM_SONGLIST_SOLO','_ENUM_SONGLIST_DUET'));
		$this->initVar('name', XOBJ_DTYPE_TXTBOX, null, false, 128);
		$this->initVar('albums', XOBJ_DTYPE_INT, 0, false);
		$this->initVar('songs', XOBJ_DTYPE_INT, 0, false);
		$this->initVar('hits', XOBJ_DTYPE_INT, 0, false);
		$this->initVar('rank', XOBJ_DTYPE_DECIMAL, 0, false);
		$this->initVar('votes', XOBJ_DTYPE_INT, 0, false);
		$this->initVar('created', XOBJ_DTYPE_INT, 0, false);
		$this->initVar('updated', XOBJ_DTYPE_INT, 0, false);
	}

	function getForm($as_array = false) {
		return songlist_artists_get_form($this, $as_array);
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


class SonglistArtistsHandler extends XoopsPersistableObjectHandler
{
    function __construct(&$db) 
    {
        parent::__construct($db, "songlist_artists", 'SonglistArtists', "aid", "name");
    }

	function filterFields() {
		return array('aid', 'cid', 'name', 'albums', 'songs', 'hits', 'rank', 'votes', 'created', 'updated');
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
    		$old = $this->get($obj->getVar('aid'));
    		$obj->setVar('updated', time());
    	}
		
    	$albums_handler = xoops_getmodulehandler('albums', 'songlist');
		$songs_handler = xoops_getmodulehandler('songs', 'songlist');
		$genre_handler = xoops_getmodulehandler('genre', 'songlist');
		$category_handler = xoops_getmodulehandler('category', 'songlist');
   	
		if (is_a($object, 'SonglistSongs')) {
			if ($obj->vars['cids']['changed']==true) {
				foreach($obj->vars['cids']['value'] as $cid) {
					if (!in_array($cid, $old->getVar('cids'))&&$cid!=0) {
						$category = $category_handler->get($cid);
		    			$category->setVar('artists', $category->getVar('artists')+1);
		    			$category_handler->insert($category, true, $obj);	
					}
				}
				foreach($old->getVar('cids') as $cid) {
					if (!in_array($cid, $obj->vars['cids']['value'])&&$cid!=0) {
						$category = $category_handler->get($cid);
		    			$category->setVar('artists', $category->getVar('artists')-1);
		    			$category_handler->insert($category, true, $obj);	
					}
				}
			}
		    	
	    	if ($object->vars['abid']['value']!=0&&$object->vars['aids']['changed']==true) {
    			$album = $albums_handler->get($object->vars['abid']['value']);
    			$album->setVar('artists', $album->getVar('artists')+1);
    			$albums_handler->insert($album, true, $obj);
	       	}
	    	
			if ($object->vars['gid']['value']!=0&&$object->vars['gid']['changed']==true) {
    			$genre = $genre_handler->get($object->vars['gid']['value']);
    			$genre->setVar('artists', $genre->getVar('artists')+1);
    			$genre_handler->insert($genre, true, $obj);
	       	}
		}
    	
    	return parent::insert($obj, $force);
    }
     
    function get($id, $fields = '*') {
    	$ret = parent::get($id, $fields);
    	if (!isset($GLOBALS['songlistAdmin'])) {
	    	$sql = 'UPDATE `'.$this->table.'` set hits=hits+1 where `'.$this->keyName.'` = '.$ret->getVar($this->keyName);
	    	$GLOBALS['xoopsDB']->queryF($sql);
    	}
    	return $ret;
    }
    
    function getObjects($criteria = NULL, $id_as_key = false, $as_object = true) {
    	$ret = parent::getObjects($criteria, $id_as_key, $as_object);
    	$id = array();
    	foreach($ret as $data) {
    		if ($as_object==true) {
    			$id[$data->getVar($this->keyName)] = $data->getVar($this->keyName);
    		} else {
    			$id[$data[$this->keyName]] = $data[$this->keyName];
    		}
    	}
    	if (!isset($GLOBALS['songlistAdmin'])) {
	    	$sql = 'UPDATE `'.$this->table.'` set hits=hits+1 where `'.$this->keyName.'` IN ('.implode(',', $id).')';
	    	$GLOBALS['xoopsDB']->queryF($sql);
    	}
    	return $ret;
    }
    
}
?>