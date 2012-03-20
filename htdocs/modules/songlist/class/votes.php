<?php

if (!defined('XOOPS_ROOT_PATH')) {
	exit();
}

class SonglistVotes extends XoopsObject
{

    function SonglistVotes($fid = null)
    {
        $this->initVar('vid', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('sid', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('uid', XOBJ_DTYPE_INT, 0, false);
		$this->initVar('ip', XOBJ_DTYPE_TXTBOX, null, false, 64);
		$this->initVar('netaddy', XOBJ_DTYPE_TXTBOX, null, false, 255);
		$this->initVar('rank', XOBJ_DTYPE_DECIMAL, 0, false);
	}

	function getForm($as_array = false) {
		return songlist_votes_get_form($this, $as_array);
	}
	
	function toArray() {
		$ret = parent::toArray();
		$form = $this->getForm(true);
		foreach($form as $key => $element) {
			$ret['form'][$key] = $form[$key]->render();	
		}
		
		$ret['rank'] = number_format($this->getVar('rank'),2)._MI_SONGLIST_OFTEN;
    		
		return $ret;
	}
}


class SonglistVotesHandler extends XoopsPersistableObjectHandler
{
    function __construct(&$db) 
    {
        parent::__construct($db, "songlist_votes", 'SonglistVotes', "vid", "ip");
    }

	function filterFields() {
		return array('vid', 'sid', 'uid', 'ip', 'netaddy', 'rank');
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
    
	function insert($obj, $force=true) {
    	if ($obj->isNew()) {
    		$obj->setVar('created', time());	
    	} else {
    		$obj->setVar('updated', time());
    	}
    	return parent::insert($obj, $force);
    }
     
}
?>