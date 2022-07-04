<?php declare(strict_types=1);

namespace XoopsModules\Songlist;

/**
 * Class CategoryHandler
 */
class CategoryHandler extends \XoopsPersistableObjectHandler
{
    /**
     * CategoryHandler constructor.
     * @param \XoopsDatabase $db
     */
    public function __construct(\XoopsDatabase $db)
    {
        parent::__construct($db, 'songlist_category', Category::class, 'cid', 'name');
    }

    /**
     * @return array
     */
    public function filterFields(): array
    {
        return ['cid', 'pid', 'weight', 'name', 'image', 'path', 'artists', 'albums', 'songs', 'hits', 'rank', 'votes', 'created', 'updated'];
    }

    /**
     * @param $filter
     * @return \CriteriaCompo
     */
    public function getFilterCriteria($filter): \CriteriaCompo
    {
        $parts    = \explode('|', $filter);
        $criteria = new \CriteriaCompo();
        foreach ($parts as $part) {
            $var = \explode(',', $part);
            if (!empty($var[1]) && !\is_numeric($var[0])) {
                $object = $this->create();
                if (\XOBJ_DTYPE_TXTBOX == $object->vars[$var[0]]['data_type']
                    || \XOBJ_DTYPE_TXTAREA == $object->vars[$var[0]]['data_type']) {
                    $criteria->add(new \Criteria('`' . $var[0] . '`', '%' . $var[1] . '%', ($var[2] ?? 'LIKE')));
                } elseif (\in_array($object->vars[$var[0]]['data_type'], [\XOBJ_DTYPE_INT, \XOBJ_DTYPE_DECIMAL, XOBJ_DTYPE_FLOAT])) {
                    $criteria->add(new \Criteria('`' . $var[0] . '`', $var[1], ($var[2] ?? '=')));
                } elseif (\XOBJ_DTYPE_ENUM == $object->vars[$var[0]]['data_type']) {
                    $criteria->add(new \Criteria('`' . $var[0] . '`', $var[1], ($var[2] ?? '=')));
                } elseif (\XOBJ_DTYPE_ARRAY == $object->vars[$var[0]]['data_type']) {
                    $criteria->add(new \Criteria('`' . $var[0] . '`', '%"' . $var[1] . '";%', ($var[2] ?? 'LIKE')));
                }
            } elseif (!empty($var[1]) && \is_numeric($var[0])) {
                $criteria->add(new \Criteria($var[0], $var[1]));
            }
        }

        return $criteria;
    }

    /**
     * @param        $filter
     * @param        $field
     * @param string $sort
     * @param string $op
     * @param string $fct
     * @return string
     */
    public function getFilterForm($filter, $field, $sort = 'created', $op = 'dashboard', $fct = 'list'): string
    {
        $ele = Utility::getFilterElement($filter, $field, $sort, $op, $fct);
        if (\is_object($ele)) {
            return $ele->render();
        }

        return '&nbsp;';
    }

    /**
     * @param int $pid
     * @return mixed
     */
    public function GetCatAndSubCat($pid = 0)
    {
        $categories  = $this->getObjects(new \Criteria('pid', $pid), true);
        $langs_array = $this->TreeIDs([], $categories, -1);

        return $langs_array;
    }

    /**
     * @param $langs_array
     * @param $categories
     * @param $level
     * @return mixed
     */
    private function TreeIDs($langs_array, $categories, $level)
    {
        foreach ($categories as $catid => $category) {
            $langs_array[$catid] = $catid;
            $categoriesb         = $this->getObjects(new \Criteria('pid', $catid), true);
            if ($categoriesb) {
                $langs_array = $this->TreeIDs($langs_array, $categoriesb, $level);
            }
        }

        return $langs_array;
    }

    /**
     * @param bool $force
     * @return bool|mixed
     */
    public function insert(\XoopsObject $obj, $force = true)
    {
        if ($obj->isNew()) {
            $obj->setVar('created', \time());
        } else {
            $obj->setVar('updated', \time());
        }
        if ('' == $obj->getVar('name')) {
            return false;
        }

        return parent::insert($obj, $force);
    }

    public $_objects = ['object' => [], 'array' => []];

    /**
     * @param null $id
     * @param null $fields
     * @return \XoopsObject
     */
    public function get($id = null, $fields = null): \XoopsObject//get($id, $fields = '*')
    {
        $fields = $fields ?: '*';
        if (!isset($this->_objects['object'][$id])) {
            $this->_objects['object'][$id] = parent::get($id, $fields);
            if (!isset($GLOBALS['songlistAdmin'])) {
                $sql = 'UPDATE `' . $this->table . '` set hits=hits+1 where `' . $this->keyName . '` = ' . $id;
                $GLOBALS['xoopsDB']->queryF($sql);
            }
        }

        return $this->_objects['object'][$id];
    }

    /**
     * @param \XoopsObject $object
     * @param bool         $force
     * @return bool
     */
    public function delete(\XoopsObject $object, $force = true): bool
    {
        parent::delete($object, $force);
        $sql = 'UPDATE ' . $GLOBALS['xoopsDB']->prefix('songlist_songs') . ' SET `cid` = 0 WHERE `cid` = ' . $object->getVar('cid');

        return $GLOBALS['xoopsDB']->queryF($sql);
    }

    /**
     * @param \CriteriaElement|\CriteriaCompo $criteria
     * @param bool $id_as_key
     * @param bool $as_object
     * @return array
     */
    public function &getObjects($criteria = null, $id_as_key = false, $as_object = true): array
    {
        $ret = parent::getObjects($criteria, $id_as_key, $as_object);

        /*if (!isset($GLOBALS['songlistAdmin'])) {
            $id = [];
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

    /**
     * @param int $limit
     * @return array
     */
    public function getTop($limit = 1): array
    {
        $sql     = 'SELECT * FROM `' . $this->table . '` WHERE `rank`>=0 ORDER BY (`rank`/`votes`) DESC LIMIT ' . $limit;
        $results = $GLOBALS['xoopsDB']->queryF($sql);
        $ret     = [];
        $i       = 0;
        while (false !== ($row = $GLOBALS['xoopsDB']->fetchArray($results))) {
            $ret[$i] = $this->create();
            $ret[$i]->assignVars($row);
            ++$i;
        }

        return $ret;
    }
}
