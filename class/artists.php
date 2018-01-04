<?php

defined('XOOPS_ROOT_PATH') || exit('Restricted access.');

include_once(dirname(__DIR__) . '/include/songlist.object.php');
include_once(dirname(__DIR__) . '/include/songlist.form.php');

class SonglistArtists extends XoopsObject
{
    public function __construct($fid = null)
    {
        $this->initVar('aid', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('cids', XOBJ_DTYPE_ARRAY, [], false);
        $this->initVar('sids', XOBJ_DTYPE_ARRAY, [], false);
        $this->initVar('name', XOBJ_DTYPE_TXTBOX, null, false, 128);
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
        return songlist_artists_get_form($this, $as_array);
    }

    public function toArray($extra = false)
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

        $ret['rank'] = number_format(($this->getVar('rank') > 0 && $this->getVar('votes') > 0 ? $this->getVar('rank') / $this->getVar('votes') : 0), 2) . _MI_SONGLIST_OFTEN;
        $ret['url']  = $this->getURL(true);

        xoops_loadLanguage('enum', 'songlist');
        if (!empty($ret['singer'])) {
            $ret['singer'] = constant($ret['singer']);
        }

        if (false === $extra) {
            return $ret;
        }

        if (0 != count($this->getVar('cids'))) {
            $categoriesHandler = xoops_getModuleHandler('category', 'songlist');
            foreach ($this->getVar('cids') as $aid) {
                $category = $categoriesHandler->get($aid);
                if (is_object($category)) {
                    $ret['categories_array'][$aid] = $category->toArray(false);
                }
            }
        }

        if (0 != count($this->getVar('aids'))) {
            $artistsHandler = xoops_getModuleHandler('artists', 'songlist');
            foreach ($this->getVar('aids') as $aid) {
                $artist = $artistsHandler->get($aid);
                if (is_object($artist)) {
                    $ret['artists_array'][$aid] = $artist->toArray(false);
                }
            }
        }

        if (0 != count($this->getVar('sids'))) {
            $songsHandler = xoops_getModuleHandler('songs', 'songlist');
            $criteria     = new Criteria('`aids`', '%"' . $this->getVar('aid') . '"%', 'LIKE');
            $criteria->setSort('`songid`');
            $criteria->setOrder('ASC');
            foreach ($songsHandler->getObjects($criteria, true) as $sid => $song) {
                if (is_object($song)) {
                    $ret['songs_array'][$sid] = $song->toArray(false);
                }
            }
        }

        return $ret;
    }

    public function getURL()
    {
        global $file, $op, $fct, $id, $value, $gid, $vid, $vcid, $cid, $start, $limit;
        if ($GLOBALS['songlistModuleConfig']['htaccess']) {
            if (0 != $id) {
                $artistHandler = xoops_getModuleHandler('artists', 'songlist');
                $artist        = $artistHandler->get($id);
                if (is_object($artist) && !$artist->isNew()) {
                    return XOOPS_URL
                           . '/'
                           . $GLOBALS['songlistModuleConfig']['baseofurl']
                           . '/artists/'
                           . urlencode(str_replace([' ', chr(9)], '-', $artist->getVar('name')))
                           . '/'
                           . $start
                           . '-'
                           . $id
                           . '-'
                           . $op
                           . '-'
                           . $fct
                           . '-'
                           . $gid
                           . '-'
                           . $cid
                           . '/'
                           . urlencode($value)
                           . $GLOBALS['songlistModuleConfig']['endofurl'];
                } else {
                    return XOOPS_URL . '/' . $GLOBALS['songlistModuleConfig']['baseofurl'] . '/artists/' . $start . '-' . $id . '-' . $op . '-' . $fct . '-' . $gid . '-' . $cid . '-' . $vcid . '/' . urlencode($value) . $GLOBALS['songlistModuleConfig']['endofurl'];
                }
            } else {
                return XOOPS_URL . '/' . $GLOBALS['songlistModuleConfig']['baseofurl'] . '/artists/' . $start . '-' . $id . '-' . $op . '-' . $fct . '-' . $gid . '-' . $cid . '-' . $vcid . '/' . urlencode($value) . $GLOBALS['songlistModuleConfig']['endofurl'];
            }
        } else {
            return XOOPS_URL . '/modules/songlist/artists.php?op=' . $op . '&fct=' . $fct . '&id=' . $id . '&value=' . urlencode($value) . '&gid=' . $gid . '&vid=' . $vid . '&cid=' . $cid . '&start=' . $start;
        }
    }
}

class SonglistArtistsHandler extends XoopsPersistableObjectHandler
{
    public function __construct($db)
    {
        parent::__construct($db, 'songlist_artists', 'SonglistArtists', 'aid', 'name');
    }

    public function filterFields()
    {
        return ['aid', 'cids', 'singer', 'name', 'albums', 'songs', 'hits', 'rank', 'votes', 'created', 'updated'];
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

    public function insert(XoopsObject $obj, $force = true, $object = null)
    {
        if ($obj->isNew()) {
            $new = true;
            $old = $this->create();
            $obj->setVar('created', time());
        } else {
            $new = false;
            $old = $this->get($obj->getVar('aid'));
            $obj->setVar('updated', time());
        }

        $albumsHandler   = xoops_getModuleHandler('albums', 'songlist');
        $songsHandler    = xoops_getModuleHandler('songs', 'songlist');
        $genreHandler    = xoops_getModuleHandler('genre', 'songlist');
        $voiceHandler    = xoops_getModuleHandler('voice', 'songlist');
        $categoryHandler = xoops_getModuleHandler('category', 'songlist');

        if (is_a($object, 'SonglistSongs')) {
            if (true === $object->vars['cid']['changed']) {
                foreach ($obj->vars['cids']['value'] as $cid) {
                    if (!in_array($cid, $object->getVar('cid')) && 0 != $cid) {
                        $obj->setVar('cids', array_merge($obj->getVar('cids'), [$object->getVar('cid') => $object->getVar('cid')]));
                        $category = $categoryHandler->get($cid);
                        if (is_object($category)) {
                            $category->setVar('artists', $category->getVar('artists') + 1);
                            $categoryHandler->insert($category, true, $obj);
                        }
                    }
                }
                if (!$old->isNew()) {
                    foreach ($old->getVar('cids') as $cid) {
                        if (!in_array($cid, $obj->vars['cids']['value']) && 0 != $cid) {
                            $category = $categoryHandler->get($cid);
                            if (is_object($category)) {
                                $category->setVar('artists', $category->getVar('artists') - 1);
                                $categoryHandler->insert($category, true, $obj);
                            }
                        }
                    }
                }
            }

            if (0 != $object->vars['abid']['value'] && true === $object->vars['aids']['changed']) {
                $album = $albumsHandler->get($object->vars['abid']['value']);
                if (is_object($album)) {
                    $album->setVar('artists', $album->getVar('artists') + 1);
                    $albumsHandler->insert($album, true, $obj);
                }
            }

            if (0 != $object->vars['gid']['value'] && true === $object->vars['gid']['changed']) {
                $genre = $genreHandler->get($object->vars['gid']['value']);
                if (is_object($genre)) {
                    $genre->setVar('artists', $genre->getVar('artists') + 1);
                    $genreHandler->insert($genre, true, $obj);
                }
            }
            if (0 != $object->vars['vid']['value'] && true === $object->vars['vid']['changed']) {
                $voice = $voiceHandler->get($object->vars['vid']['value']);
                if (is_object($voice)) {
                    $voice->setVar('artists', $voice->getVar('artists') + 1);
                    $voiceHandler->insert($voice, true, $obj);
                }
            }
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
            if (!isset($GLOBALS['songlistAdmin']) && is_object($this->_objects['object'][$id])) {
                $sql = 'UPDATE `' . $this->table . '` set hits=hits+1 where `' . $this->keyName . '` = ' . $this->_objects['object'][$id]->getVar($this->keyName);
                $GLOBALS['xoopsDB']->queryF($sql);
            }
        }

        return $this->_objects['object'][$id];
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

    public function getURL()
    {
        global $file, $op, $fct, $id, $value, $gid, $vid, $cid, $start, $limit;
        if ($GLOBALS['songlistModuleConfig']['htaccess']) {
            if (0 != $cid) {
                $artistHandler = xoops_getModuleHandler('artists', 'songlist');
                $artist        = $artistHandler->get($cid);
                if (is_object($artist) && !$artist->isNew()) {
                    return XOOPS_URL
                           . '/'
                           . $GLOBALS['songlistModuleConfig']['baseofurl']
                           . '/'
                           . $file
                           . '/'
                           . urlencode(str_replace([' ', chr(9)], '-', $artist->getVar('name')))
                           . '/'
                           . $start
                           . '-'
                           . $id
                           . '-'
                           . $op
                           . '-'
                           . $fct
                           . '-'
                           . $gid
                           . '-'
                           . $cid
                           . '/'
                           . urlencode($value)
                           . $GLOBALS['songlistModuleConfig']['endofurl'];
                } else {
                    return XOOPS_URL . '/' . $GLOBALS['songlistModuleConfig']['baseofurl'] . '/' . $file . '/' . $start . '-' . $id . '-' . $op . '-' . $fct . '-' . $gid . '-' . $cid . '/' . urlencode($value) . $GLOBALS['songlistModuleConfig']['endofurl'];
                }
            } else {
                return XOOPS_URL . '/' . $GLOBALS['songlistModuleConfig']['baseofurl'] . '/' . $file . '/' . $start . '-' . $id . '-' . $op . '-' . $fct . '-' . $gid . '-' . $cid . '/' . urlencode($value) . $GLOBALS['songlistModuleConfig']['endofurl'];
            }
        } else {
            return XOOPS_URL . '/modules/songlist/' . $file . '.php?op=' . $op . '&fct=' . $fct . '&id=' . $id . '&value=' . urlencode($value) . '&gid=' . $gid . '&vid=' . $vid . '&cid=' . $cid . '&start=' . $start;
        }
    }

    public function getSIDs($criteria = null)
    {
        $ret         = [];
        $songHandler = xoops_getModuleHandler('songs', 'songlist');
        foreach ($this->getObjects($criteria, true) as $aid => $object) {
            $crita = new Criteria('`aids`', '%"' . $aid . '"%', 'LIKE');
            foreach ($songHandler->getObjects($crita, true) as $sid => $song) {
                $ret[$sid] = $sid;
            }
        }

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
