<?php declare(strict_types=1);

namespace XoopsModules\Songlist;

require_once \dirname(__DIR__) . '/include/songlist.object.php';
// require_once \dirname(__DIR__) . '/include/songlist.form.php';
use  XoopsModules\Songlist\Form\FormController;

/**
 * Class VoiceHandler
 */
class VoiceHandler extends \XoopsPersistableObjectHandler
{
    /**
     * VoiceHandler constructor.
     * @param \XoopsDatabase $db
     */
    public function __construct(\XoopsDatabase $db)
    {
        parent::__construct($db, 'songlist_voice', Voice::class, 'vcid', 'name');
    }

    /**
     * @return array
     */
    public function filterFields(): array
    {
        return ['vcid', 'name', 'artists', 'albums', 'songs', 'hits', 'rank', 'votes', 'created', 'updated'];
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
                } elseif (in_array($object->vars[$var[0]]['data_type'], [XOBJ_DTYPE_INT, XOBJ_DTYPE_DECIMAL, XOBJ_DTYPE_FLOAT])) {
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
            if (!isset($GLOBALS['songlistAdmin']) && \is_object($this->_objects['object'][$id])) {
                $sql = 'UPDATE `' . $this->table . '` set hits=hits+1 where `' . $this->keyName . '` = ' . $this->_objects['object'][$id]->getVar($this->keyName);
                $GLOBALS['xoopsDB']->queryF($sql);
            }
        }

        return $this->_objects['object'][$id];
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
     * @return string
     */
    public function getURL(): string
    {
        global $file, $op, $fct, $id, $value, $gid, $cid, $start, $limit;
        if ($GLOBALS['songlistModuleConfig']['htaccess']) {
            return XOOPS_URL . '/' . $GLOBALS['songlistModuleConfig']['baseurl'] . '/' . $file . '/' . $start . '-' . $op . '-' . $fct . '-' . $id . '-' . \urlencode($value) . '-' . $gid . '-' . $cid . $GLOBALS['songlistModuleConfig']['endofurl'];
        }

        return XOOPS_URL . '/modules/songlist/' . $file . '.php?op=' . $op . '&fct=' . $fct . '&id=' . $id . '&value=' . \urlencode($value ?? '') . '&gid=' . $gid . '&cid=' . $cid . '&start=' . $start;
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

    /**
     * @param \XoopsObject $object
     * @param bool         $force
     * @return bool
     */
    public function delete(\XoopsObject $object, $force = true): bool
    {
        parent::delete($object, $force);
        $sql = 'UPDATE ' . $GLOBALS['xoopsDB']->prefix('songlist_songs') . ' SET `vcid` = 0 WHERE `vcid` = ' . $object->getVar('vcid');

        return $GLOBALS['xoopsDB']->queryF($sql);
    }
}
