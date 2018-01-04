<?php

defined('XOOPS_ROOT_PATH') || exit('Restricted access.');

class SonglistCategory extends XoopsObject
{
    public function __construct($fid = null)
    {
        $this->initVar('cid', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('pid', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('weight', XOBJ_DTYPE_INT, 1, false);
        $this->initVar('name', XOBJ_DTYPE_TXTBOX, null, false, 128);
        $this->initVar('description', XOBJ_DTYPE_OTHER, null, false);
        $this->initVar('image', XOBJ_DTYPE_TXTBOX, null, false, 128);
        $this->initVar('path', XOBJ_DTYPE_TXTBOX, null, false, 128);
        $this->initVar('artists', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('albums', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('songs', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('hits', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('rank', XOBJ_DTYPE_DECIMAL, 0, false);
        $this->initVar('votes', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('created', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('updated', XOBJ_DTYPE_INT, 0, false);
    }

    public function getForm($as_array = false)
    {
        return songlist_category_get_form($this, $as_array);
    }

    public function toArray()
    {
        $ret  = parent::toArray();
        $form = $this->getForm(true);
        foreach ($form as $key => $element) {
            $ret['form'][$key] = $form[$key]->render();
        }
        foreach (['created', 'updated'] as $key) {
            if ($this->getVar($key) > 0) {
                $ret['form'][$key] = date(_DATESTRING, $this->getVar($key));
                $ret[$key]         = date(_DATESTRING, $this->getVar($key));
            }
        }
        $ret['picture']  = $this->getImage('image', false);
        $ret['rank']     = number_format(($this->getVar('rank') > 0 && $this->getVar('votes') > 0 ? $this->getVar('rank') / $this->getVar('votes') : 0), 2) . _MI_SONGLIST_OFTEN;
        $ret['url']      = $this->getURL();
        $categoryHandler = xoops_getModuleHandler('category', 'songlist');
        if ($categoryHandler->getCount(new Criteria('pid', $this->getVar('cid')))) {
            foreach ($categoryHandler->getObjects(new Criteria('pid', $this->getVar('cid')), true) as $cid => $cat) {
                $ret['subcategories'][$cid] = $cat->toArray();
            }
        }

        return $ret;
    }

    public function getImage($field = 'image', $local = false)
    {
        if (0 == strlen($this->getVar($field))) {
            return false;
        }
        if (!file_exists($GLOBALS['xoops']->path($this->getVar('path') . $this->getVar($field)))) {
            return false;
        }
        if (false === $local) {
            return XOOPS_URL . '/' . str_replace(DS, '/', $this->getVar('path')) . $this->getVar($field);
        } else {
            return XOOPS_ROOT_PATH . DS . $this->getVar('path') . $this->getVar($field);
        }
    }

    public function getURL()
    {
        global $file, $op, $fct, $id, $value, $gid, $cid, $start, $limit;

        return XOOPS_URL . '/modules/songlist/' . $file . '.php?op=category&fct=set&id=' . $this->getVar('cid') . '&value=' . urlencode($file) . '&gid=' . $gid . '&cid=' . $cid;
    }
}

class SonglistCategoryHandler extends XoopsPersistableObjectHandler
{
    public function __construct($db)
    {
        parent::__construct($db, 'songlist_category', 'SonglistCategory', 'cid', 'name');
    }

    public function filterFields()
    {
        return ['cid', 'pid', 'weight', 'name', 'image', 'path', 'artists', 'albums', 'songs', 'hits', 'rank', 'votes', 'created', 'updated'];
    }

    public function getFilterCriteria($filter)
    {
        $parts    = explode('|', $filter);
        $criteria = new CriteriaCompo();
        foreach ($parts as $part) {
            $var = explode(',', $part);
            if (!empty($var[1]) && !is_numeric($var[0])) {
                $object = $this->create();
                if (XOBJ_DTYPE_TXTBOX == $object->vars[$var[0]]['data_type']
                    || XOBJ_DTYPE_TXTAREA == $object->vars[$var[0]]['data_type']) {
                    $criteria->add(new Criteria('`' . $var[0] . '`', '%' . $var[1] . '%', (isset($var[2]) ? $var[2] : 'LIKE')));
                } elseif (XOBJ_DTYPE_INT == $object->vars[$var[0]]['data_type']
                          || XOBJ_DTYPE_DECIMAL == $object->vars[$var[0]]['data_type']
                          || XOBJ_DTYPE_FLOAT == $object->vars[$var[0]]['data_type']) {
                    $criteria->add(new Criteria('`' . $var[0] . '`', $var[1], (isset($var[2]) ? $var[2] : '=')));
                } elseif (XOBJ_DTYPE_ENUM == $object->vars[$var[0]]['data_type']) {
                    $criteria->add(new Criteria('`' . $var[0] . '`', $var[1], (isset($var[2]) ? $var[2] : '=')));
                } elseif (XOBJ_DTYPE_ARRAY == $object->vars[$var[0]]['data_type']) {
                    $criteria->add(new Criteria('`' . $var[0] . '`', '%"' . $var[1] . '";%', (isset($var[2]) ? $var[2] : 'LIKE')));
                }
            } elseif (!empty($var[1]) && is_numeric($var[0])) {
                $criteria->add(new Criteria($var[0], $var[1]));
            }
        }

        return $criteria;
    }

    public function getFilterForm($filter, $field, $sort = 'created', $op = 'dashboard', $fct = 'list')
    {
        $ele = songlist_getFilterElement($filter, $field, $sort, $op, $fct);
        if (is_object($ele)) {
            return $ele->render();
        } else {
            return '&nbsp;';
        }
    }

    public function GetCatAndSubCat($pid = 0)
    {
        $categories  = $this->getObjects(new Criteria('pid', $pid), true);
        $langs_array = $this->TreeIDs([], $categories, -1);

        return $langs_array;
    }

    private function TreeIDs($langs_array, $categories, $level)
    {
        foreach ($categories as $catid => $category) {
            $langs_array[$catid] = $catid;
            if ($categoriesb = $this->getObjects(new Criteria('pid', $catid), true)) {
                $langs_array = $this->TreeIDs($langs_array, $categoriesb, $level);
            }
        }

        return ($langs_array);
    }

    public function insert(XoopsObject $obj, $force = true)
    {
        if ($obj->isNew()) {
            $obj->setVar('created', time());
        } else {
            $obj->setVar('updated', time());
        }
        if (0 == strlen($obj->getVar('name'))) {
            return false;
        }

        return parent::insert($obj, $force);
    }

    public $_objects = ['object' => [], 'array' => []];

    public function get($id, $fields = '*')
    {
        if (!isset($this->_objects['object'][$id])) {
            $this->_objects['object'][$id] = parent::get($id, $fields);
            if (!isset($GLOBALS['songlistAdmin'])) {
                $sql = 'UPDATE `' . $this->table . '` set hits=hits+1 where `' . $this->keyName . '` = ' . $id;
                $GLOBALS['xoopsDB']->queryF($sql);
            }
        }

        return $this->_objects['object'][$id];
    }

    public function delete(XoopsObject $object, $force = true)
    {
        parent::delete($object, $force);
        $sql = 'UPDATE ' . $GLOBALS['xoopsDB']->prefix('songlist_songs') . ' SET `cid` = 0 WHERE `cid` = ' . $object->getVar('cid');

        return $GLOBALS['xoopsDB']->queryF($sql);
    }

    public function getObjects($criteria = null, $id_as_key = false, $as_object = true)
    {
        $ret = parent::getObjects($criteria, $id_as_key, $as_object);

        /*if (!isset($GLOBALS['songlistAdmin'])) {
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
        }*/

        return $ret;
    }

    public function getTop($limit = 1)
    {
        $sql     = 'SELECT * FROM `' . $this->table . '` WHERE `rank`>=0 ORDER BY (`rank`/`votes`) DESC LIMIT ' . $limit;
        $results = $GLOBALS['xoopsDB']->queryF($sql);
        $ret     = [];
        $i       = 0;
        while ($row = $GLOBALS['xoopsDB']->fetchArray($results)) {
            $ret[$i] = $this->create();
            $ret[$i]->assignVars($row);
            ++$i;
        }

        return $ret;
    }
}
