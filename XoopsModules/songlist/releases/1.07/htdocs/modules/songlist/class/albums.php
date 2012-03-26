<?php

if (!defined('XOOPS_ROOT_PATH')) {
	exit();
}

include_once(dirname(dirname(__FILE__)).'/include/songlist.object.php');
include_once(dirname(dirname(__FILE__)).'/include/songlist.form.php');

class SonglistAlbums extends XoopsObject
{

    function SonglistAlbums($fid = null)
    {
        $this->initVar('abid', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('cid', XOBJ_DTYPE_INT, 0, false);
		$this->initVar('aids', XOBJ_DTYPE_ARRAY, array(), false);
		$this->initVar('sids', XOBJ_DTYPE_ARRAY, array(), false);
		$this->initVar('title', XOBJ_DTYPE_TXTBOX, null, false, 128);
		$this->initVar('image', XOBJ_DTYPE_TXTBOX, null, false, 255);
		$this->initVar('path', XOBJ_DTYPE_TXTBOX, null, false, 255);
		$this->initVar('artists', XOBJ_DTYPE_INT, 0, false);
		$this->initVar('songs', XOBJ_DTYPE_INT, 0, false);
		$this->initVar('hits', XOBJ_DTYPE_INT, 0, false);
		$this->initVar('rank', XOBJ_DTYPE_DECIMAL, 0, false);
		$this->initVar('votes', XOBJ_DTYPE_INT, 0, false);
		$this->initVar('created', XOBJ_DTYPE_INT, 0, false);
		$this->initVar('updated', XOBJ_DTYPE_INT, 0, false);
	}

	function getForm($as_array = false) {
		return songlist_albums_get_form($this, $as_array);
	}
	
	function toArray($extra = true) {
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
		$ret['picture'] = $this->getImage('image', false);
		$ret['rank'] = number_format(($this->getVar('rank')>0&&$this->getVar('votes')>0?$this->getVar('rank')/$this->getVar('votes'):0),2)._MI_SONGLIST_OFTEN;
    	$ret['url'] = $this->getURL();
    	
		if ($extra==false)
    		return $ret;
    		
    	if ($this->getVar('cid')!=0) {
    		$category_handler = xoops_getmodulehandler('category', 'songlist');
    		$category = $category_handler->get($this->getVar('cid'));
    		$ret['category'] = $category->toArray(false); 	
    	}

    	
		if (count($this->getVar('aids'))!=0) {
    		$artists_handler = xoops_getmodulehandler('artists', 'songlist');
    		foreach($this->getVar('aids') as $aid) {
    			$artist = $artists_handler->get($aid);
    			$ret['artists_array'][$aid] = $artist->toArray(false);
    		} 	
    	}
    	
		
		if (count($this->getVar('sids'))!=0) {
    		$songs_handler = xoops_getmodulehandler('songs', 'songlist');
    		foreach($this->getVar('sids') as $sid) {
    			$song = $songs_handler->get($sid);
    			$ret['songs_array'][$sid] = $song->toArray(false);
    		} 	
    	}
    	
    	return $ret;
		
	}
    
	function getImage($field = 'image', $local = false) {
		if (strlen($this->getVar($field))==0)
			return false;
		if (!file_exists($GLOBALS['xoops']->path($this->getVar('path').$this->getVar($field))))
			return false;
		if ($local==false)
    		return XOOPS_URL.'/'.str_replace(DS, '/', $this->getVar('path')).$this->getVar($field);
    	else 
    		return XOOPS_ROOT_PATH.DS.$this->getVar('path').$this->getVar($field);
    }
	
	function getURL() {
    	global $file, $op, $fct, $id, $value, $gid, $cid, $start, $limit;
    	if ($GLOBALS['songlistModuleConfig']['htaccess']) {
    		return XOOPS_URL.'/'.$GLOBALS['songlistModuleConfig']['baseurl'].'/'.$file.'/'.urlencode(str_replace(array(' ', chr(9)), '-', $this->getVar('title'))).'/'.$op.'-'.$fct.'-'.$this->getVar('abid').'-'.urlencode($value).'-'.$gid.'-'.$cid.$GLOBALS['songlistModuleConfig']['endofurl'];
    	} else {
    		return XOOPS_URL.'/modules/songlist/'.$file.'.php?op='.$op.'&fct='.$fct.'&id='.$this->getVar('abid').'&value='.urlencode($value).'&gid='.$gid.'&cid='.$cid;
    	}
    }
        
}


class SonglistAlbumsHandler extends XoopsPersistableObjectHandler
{
    function __construct(&$db) 
    {
        parent::__construct($db, "songlist_albums", 'SonglistAlbums', "abid", "title");
    }
    
	function filterFields() {
		return array('abid', 'cid', 'aids', 'sids', 'title', 'image', 'path', 'artists', 'songs', 'hits', 'rank', 'votes', 'created', 'updated');
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
    		$old = $this->get($obj->getVar('abid'));
    		$obj->setVar('updated', time());
    	}
		
		$artists_handler = xoops_getmodulehandler('artists', 'songlist');
		$genre_handler = xoops_getmodulehandler('genre', 'songlist');
		$category_handler = xoops_getmodulehandler('category', 'songlist');
   	
		if (is_a($object, 'SonglistSongs')) {
			if ($obj->vars['cid']['changed']==true) {
				if ($obj->vars['cid']['value'] != $old->vars['cid']['value']) {
					$category = $category_handler->get($obj->vars['cid']['value']);
		    		$category->setVar('albums', $category->getVar('albums')+1);
		    		$category_handler->insert($category, true, $obj);
		    		if (!$old->isNew()&&$old->vars['cid']['value']>0) {
			    		$category = $category_handler->get($old->vars['cid']['value']);
			    		$category->setVar('albums', $category->getVar('albums')-1);
			    		$category_handler->insert($category, true, $obj);
		    		}	
				}
			}
		    	
	    	if (count($obj->vars['aids']['value'])!=0&&$obj->vars['aids']['changed']==true) {
	    		foreach($obj->vars['aids']['value'] as $aid) {
	    			if (!is_array($aid, $old->getVar('aids'))&&$aid!=0) {
		    			$artists = $artists_handler->get($aid);
		    			$artists->setVar('albums', $artists->getVar('albums')+1);
		    			$artists_handler->insert($artists, true, $obj);
	    			}
	    		}
	    		if (!$old->isNew()) {
		    		foreach($old->getVar('aids') as $aid) {
		    			if (!is_array($aid, $obj->vars['aids']['value'])&&$aid!=0) {
			    			$artists = $artists_handler->get($aid);
			    			$artists->setVar('albums', $artists->getVar('albums')-1);
			    			$artists_handler->insert($artists, true, $obj);
		    			}
		    		}
	    		}
	       	}
	    	
			if ($object->vars['gid']['value']!=0&&$object->vars['gid']['changed']==true) {
    			$genre = $genre_handler->get($object->vars['gid']['value']);
    			$genre->setVar('albums', $genre->getVar('albums')+1);
    			$genre_handler->insert($genre, true, $obj);
	       	}
		}
		if (strlen($obj->getVar('title'))==0)
    		return false;
    	
    	return parent::insert($obj, $force);
    }
     
	var $_objects = array('object'=>array(), 'array'=>array());
    
    function get($id, $fields = '*') {
    	if (!isset($this->_objects['object'][$id])) {
	    	$this->_objects['object'][$id] = parent::get($id, $fields);
	    	if (!isset($GLOBALS['songlistAdmin'])) {
		    	$sql = 'UPDATE `'.$this->table.'` set hits=hits+1 where `'.$this->keyName.'` = '.$this->_objects['object'][$id]->getVar($this->keyName);
		    	$GLOBALS['xoopsDB']->queryF($sql);
	    	}
    	}
    	return $this->_objects['object'][$id];
    }
    
    function getObjects($criteria = NULL, $id_as_key = false, $as_object = true) {
    	$ret = parent::getObjects($criteria, $id_as_key, $as_object);
   		 if (!isset($GLOBALS['songlistAdmin'])) {
	    	$id = array();
	    	foreach($ret as $data) {
	    		if ($as_object==true) {
	    			if (!in_array($data->getVar($this->keyName), array_keys($this->_objects['object']))) {
	    				$this->_objects['object'][$data->getVar($this->keyName)] = $data;
	    				$id[$data->getVar($this->keyName)] = $data->getVar($this->keyName);
	    			}
	    		} else {
	    			if (!in_array($data[$this->keyName], array_keys($this->_objects['array']))) {
	    				$this->_objects['array'][$data[$this->keyName]] = $data;
	    				$id[$data[$this->keyName]] = $data[$this->keyName];;
	    			}
	    		}
	    	}
    	}
    	if (!isset($GLOBALS['songlistAdmin'])&&count($id)>0) {
	    	$sql = 'UPDATE `'.$this->table.'` set hits=hits+1 where `'.$this->keyName.'` IN ('.implode(',', $id).')';
	    	$GLOBALS['xoopsDB']->queryF($sql);
    	}
    	return $ret;
    }
    
    function getURL() {
    	global $file, $op, $fct, $id, $value, $gid, $cid, $start, $limit;
    	if ($GLOBALS['songlistModuleConfig']['htaccess']) {
    		return XOOPS_URL.'/'.$GLOBALS['songlistModuleConfig']['baseurl'].'/albums/'.$start.'-'.$op.'-'.$fct.'-'.$id.'-'.urlencode($value).'-'.$gid.'-'.$cid.$GLOBALS['songlistModuleConfig']['endofurl'];
    	} else {
    		return XOOPS_URL.'/modules/songlist/albums.php?op='.$op.'&fct='.$fct.'&id='.$id.'&value='.urlencode($value).'&gid='.$gid.'&cid='.$cid;
    	}
    }
    
    function getTop($limit=1) {
    	$sql = 'SELECT * FROM `'.$this->table.'` WHERE `rank`>=0 ORDER BY (`rank`/`votes`) DESC LIMIT '.$limit;
    	$results = $GLOBALS['xoopsDB']->queryF($sql);
    	$ret = array();
    	$i=0;
    	while ($row = $GLOBALS['xoopsDB']->fetchArray($results)) {
    		$ret[$i] = $this->create();
    		$ret[$i]->assignVars($row);
    		$i++;
    	}
    	return $ret;
    }
}
?>