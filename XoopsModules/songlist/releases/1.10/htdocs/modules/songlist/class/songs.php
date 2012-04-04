<?php

if (!defined('XOOPS_ROOT_PATH')) {
	exit();
}

include_once(dirname(dirname(__FILE__)).'/include/songlist.object.php');
include_once(dirname(dirname(__FILE__)).'/include/songlist.form.php');

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
		$this->initVar('lyrics', XOBJ_DTYPE_TXTBOX, null, false, 650);		
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
		
		$ret['url'] = $this->getURL();
		
		$ret['rank'] = number_format(($this->getVar('rank')>0&&$this->getVar('votes')>0?$this->getVar('rank')/$this->getVar('votes'):0),2)._MI_SONGLIST_OFTEN;
		
		if (file_exists($GLOBALS['xoops']->path("/modules/tag/include/tagbar.php"))&&$GLOBALS['songlistModuleConfig']['tags']) {
			include_once XOOPS_ROOT_PATH."/modules/tag/include/tagbar.php";
			$ret['tagbar'] = tagBar($this->getVar('sid'), $catid = 0);
		}
		
		$extras_handler = xoops_getmodulehandler('extras', 'songlist');
		$field_handler = xoops_getmodulehandler('field', 'songlist');
		$visibility_handler = xoops_getmodulehandler('visibility', 'songlist');		

		if ($extras = $extras_handler->get($this->getVar('sid'))) {
	
			if (is_object($GLOBALS['xoopsUser']))
				$fields_id = $visibility_handler->getVisibleFields(array(), $GLOBALS['xoopsUser']->getGroups());
			elseif (!is_object($GLOBALS['xoopsUser']))
				$fields_id = $visibility_handler->getVisibleFields(array(), array());
	
			$criteria = new Criteria('field_id', '('.implode(',',$fields_id).')', 'IN');
			$criteria->setSort('field_weight');
			$fields = $field_handler->getObjects($criteria, true);
			foreach($fields as $id => $field) {
				if (in_array($this->getVar('cid'), $field->getVar('cids'))) {
					$ret['fields'][$id]['title'] = $field->getVar('field_title');
					if (is_object($GLOBALS['xoopsUser']))
						$ret['fields'][$id]['value'] = htmlspecialchars_decode($field->getOutputValue($GLOBALS['xoopsUser'], $extras));
					elseif (!is_object($GLOBALS['xoopsUser']))
						$ret['fields'][$id]['value'] = htmlspecialchars_decode($extras->getVar($field->getVar('field_name')));			
				}
			}
		}
				
    	if ($extra==false)
    		return $ret;
    		
    	if ($this->getVar('cid')!=0) {
    		$category_handler = xoops_getmodulehandler('category', 'songlist');
    		$category = $category_handler->get($this->getVar('cid'));
    		$ret['category'] = $category->toArray(false); 	
    	}

    	if ($this->getVar('gid')!=0) {
    		$genre_handler = xoops_getmodulehandler('genre', 'songlist');
    		$genre = $genre_handler->get($this->getVar('gid'));
    		$ret['genre'] = $genre->toArray(false); 	
    	}
    	
		if (count($this->getVar('aids'))!=0) {
    		$artists_handler = xoops_getmodulehandler('artists', 'songlist');
    		foreach($this->getVar('aids') as $aid) {
    			$artist = $artists_handler->get($aid);
    			$ret['artists_array'][$aid] = $artist->toArray(false);
    		} 	
    	}
    	
		if ($this->getVar('abid')!=0) {
    		$albums_handler = xoops_getmodulehandler('albums', 'songlist');
    		$albums = $albums_handler->get($this->getVar('abid'));
    		$ret['album'] = $albums->toArray(false); 	
    	}
    	return $ret;
	}
	
	function getURL() {
    	global $file, $op, $fct, $id, $value, $gid, $cid, $start, $limit;
    	if ($GLOBALS['songlistModuleConfig']['htaccess']) {
    		return XOOPS_URL.'/'.$GLOBALS['songlistModuleConfig']['baseofurl'].'/index/'.urlencode(str_replace(array(' ', chr(9)), '-', $this->getVar('title'))).'/item-item-'.$this->getVar('sid').'-'.urlencode($value).'-'.$gid.'-'.$cid.$GLOBALS['songlistModuleConfig']['endofurl'];
    	} else {
    		return XOOPS_URL.'/modules/songlist/index.php?op=item&fct=item&id='.$this->getVar('sid').'&value='.urlencode($value).'&gid='.$gid.'&cid='.$cid;
    	}
    }
	
}


class SonglistSongsHandler extends XoopsPersistableObjectHandler
{
    function __construct(&$db) 
    {
        parent::__construct($db, "songlist_songs", 'SonglistSongs', "sid", "title");
    }

	function filterFields() {
		return array('sid', 'cid', 'gid', 'aids', 'abid', 'songid', 'title', 'lyrics', 'hits', 'rank', 'votes', 'tags', 'created', 'updated');
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
    		if ($new==true||($obj->vars['gid']['value']!=0)) {
    			$genre = $genre_handler->get($obj->vars['gid']['value']);
    			$genre->setVar('songs', $genre->getVar('songs')+1);
    			$genre_handler->insert($genre, true, $obj);
    		} 
    		if (!$old->isNew()&&$old->getVar('gid')>0) {
    			$genre = $genre_handler->get($old->vars['gid']['value']);
    			$genre->setVar('songs', $genre->getVar('songs')-1);
    			$genre_handler->insert($genre, true, null);
    		}
    	}
    	
		if ($obj->vars['cid']['changed']==true) {
    		if ($new==true||($obj->vars['cid']['value']!=0)) {
    			$category = $category_handler->get($obj->vars['cid']['value']);
    			$category->setVar('songs', $category->getVar('songs')+1);
    			$category_handler->insert($category, true, $obj);
    		    foreach($obj->getVar('aids') as $aid) {
	    			$artists = $artists_handler->get($aid);
	    			$cids = $artists->getVar('cids');
	    			$cids[$obj->getVar('cid')] = $obj->getVar('cid');
	    			$artists->setVar('cids', $cids);
	    			$artists_handler->insert($artists, true, null);
    			}
    		}
			if (!$old->isNew()&&$old->getVar('cid')>0) {
				$category = $category_handler->get($old->vars['cid']['value']);
    			$category->setVar('songs', $category->getVar('songs')-1);
    			$category_handler->insert($category, true, null);
				foreach($obj->getVar('aids') as $aid) {
	    			$artists = $artists_handler->get($aid);
	    			$cids=array();
	    			foreach($artists->getVar('cids') as $cid) {
	    				if($cid!=$old->getVar('cid')||$cid==$obj->getVar('cid'))
	    					$cids[$cid] = $cid;
	    			}
	    			$artists->setVar('cids', $cids);
	    			$artists_handler->insert($artists, true, null);
    			}
    		}
    	}
    	
    	if ($obj->vars['aids']['changed']==true&&count($obj->vars['aids']['value'])!=0) {
    		if ($new==true||count($obj->vars['aids']['value'])!=0) {
    			foreach($obj->vars['aids']['value'] as $aid) {
    				if (!in_array($aid, $old->vars['aids']['value'])) {
		    			$artists = $artists_handler->get($aid);
		    			$artists->setVar('songs', $artists->getVar('songs')+1);
		    			$artists_handler->insert($artists, true, $obj);
    				}
    			}
    		}
    		if (!$old->isNew()&&count($old->getVar('aids'))==0) {
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
    		if ($new==true||($obj->vars['abid']['value']!=0)) {
    			$album = $albums_handler->get($obj->vars['abid']['value']);
    			$album->setVar('songs', $album->getVar('songs')+1);
    			$albums_handler->insert($album, true, $obj);
    		}
    		if (!$old->isNew()&&$old->getVar('abid')>0) {
    			$album = $albums_handler->get($obj->vars['abid']['value']);
    			$album->setVar('songs', $album->getVar('songs')-1);
    			$albums_handler->insert($album, true, null);
    		}
    	}
    	
    	if (strlen($obj->getVar('title'))==0)
    		return false;
    	
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
    	/*if (!isset($GLOBALS['songlistAdmin'])) {
	    	foreach($ret as $data) {
	    		$id = array();
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
    	}*/
    	return $ret;
    }   
    
    function getURL() {
    	global $file, $op, $fct, $id, $value, $gid, $cid, $start, $limit;
    	if ($GLOBALS['songlistModuleConfig']['htaccess']) {
    		return XOOPS_URL.'/'.$GLOBALS['songlistModuleConfig']['baseofurl'].'/'.$file.'/'.$start.'-'.$op.'-'.$fct.'-'.$id.'-'.urlencode($value).'-'.$gid.'-'.$cid.$GLOBALS['songlistModuleConfig']['endofurl'];
    	} else {
    		return XOOPS_URL.'/modules/songlist/'.$file.'.php?op='.$op.'&fct='.$fct.'&id='.$id.'&value='.urlencode($value).'&gid='.$gid.'&cid='.$cid.'&start='.$start;
    	}
    }

    function getSearchURL() {
    	global $file, $op, $fct, $id, $value, $gid, $cid, $singer, $start, $limit;
    	if ($GLOBALS['songlistModuleConfig']['htaccess']) {
    		return XOOPS_URL.'/'.$GLOBALS['songlistModuleConfig']['baseofurl'].'/'.$file.'/'.$start.'-'.$op.'-'.$fct.'-'.urlencode($value).'-'.(isset($_GET['cid'])?($_GET['cid']):$cid).'-'.$gid.'-'.$singer.$GLOBALS['songlistModuleConfig']['endofurl'];
    	} else {
    		return XOOPS_URL.'/modules/songlist/'.$file.'.php?op='.$op.'&fct='.$fct.'&value='.urlencode($value).'&cid='.(isset($_GET['cid'])?($_GET['cid']):$cid).'&gid='.$gid.'&singer='.$singer.'&start='.$start;
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