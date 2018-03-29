<?php

defined('XOOPS_ROOT_PATH') || die('Restricted access');

include_once(dirname(__DIR__) . '/include/songlist.object.php');
include_once(dirname(__DIR__) . '/include/songlist.form.php');

class SonglistSongs extends XoopsObject
{
    public function __construct($fid = null)
    {
        $this->initVar('sid', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('cid', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('gids', XOBJ_DTYPE_ARRAY, 0, false);
        $this->initVar('vcid', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('aids', XOBJ_DTYPE_ARRAY, [], false);
        $this->initVar('abid', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('songid', XOBJ_DTYPE_TXTBOX, null, false, 32);
        $this->initVar('traxid', XOBJ_DTYPE_TXTBOX, null, false, 32);
        $this->initVar('title', XOBJ_DTYPE_TXTBOX, null, false, 128);
        $this->initVar('lyrics', XOBJ_DTYPE_OTHER, null, false);
        $this->initVar('hits', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('rank', XOBJ_DTYPE_DECIMAL, 0, false);
        $this->initVar('votes', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('tags', XOBJ_DTYPE_TXTBOX, null, false, 255);
        $this->initVar('mp3', XOBJ_DTYPE_OTHER, null, false, 500);
        $this->initVar('created', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('updated', XOBJ_DTYPE_INT, 0, false);
    }

    public function getForm($as_array = false)
    {
        return songlist_songs_get_form($this, $as_array);
    }

    public function toArray($extra = true)
    {
        $ret = parent::toArray();

        $GLOBALS['myts'] = \MyTextSanitizer::getInstance();

        $ret['lyrics'] = $GLOBALS['myts']->displayTarea($this->getVar('lyrics'), true, true, true, true, true);

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

        $ret['url'] = $this->getURL();

        $ret['rank'] = number_format(($this->getVar('rank') > 0 && $this->getVar('votes') > 0 ? $this->getVar('rank') / $this->getVar('votes') : 0), 2) . _MI_SONGLIST_OFTEN;

        if (!empty($ret['mp3'])) {
            $ret['mp3'] = '<embed flashvars="playerID=1&amp;bg=0xf8f8f8&amp;leftbg=0x3786b3&amp;lefticon=0x78bee3&amp;rightbg=0x3786b3&amp;rightbghover=0x78bee3&amp;righticon=0x78bee3&amp;righticonhover=0x3786b3&amp;text=0x666666&amp;slider=0x3786b3&amp;track=0xcccccc&amp;border=0x666666&amp;loader=0x78bee3&amp;loop=no&amp;soundFile='
                          . $ret['mp3']
                          . "\" quality='high' menu='false' wmode='transparent' pluginspage='http://www.macromedia.com/go/getflashplayer' src='"
                          . XOOPS_URL
                          . "/images/form/player.swf'  width=290 height=24 type='application/x-shockwave-flash'></embed>";
        }

        if (file_exists($GLOBALS['xoops']->path('modules/tag/include/tagbar.php')) && $GLOBALS['songlistModuleConfig']['tags']) {
            require_once XOOPS_ROOT_PATH . '/modules/tag/include/tagbar.php';
            $ret['tagbar'] = tagBar($this->getVar('sid'), $this->getVar('cid'));
        }

        $extrasHandler     = xoops_getModuleHandler('extras', 'songlist');
        $fieldHandler      = xoops_getModuleHandler('field', 'songlist');
        $visibilityHandler = xoops_getModuleHandler('visibility', 'songlist');

        if ($extras = $extrasHandler->get($this->getVar('sid'))) {
            if (is_object($GLOBALS['xoopsUser'])) {
                $fields_id = $visibilityHandler->getVisibleFields([], $GLOBALS['xoopsUser']->getGroups());
            } elseif (!is_object($GLOBALS['xoopsUser'])) {
                $fields_id = $visibilityHandler->getVisibleFields([], []);
            }

            if (count($fields_id) > 0) {
                $criteria = new \Criteria('field_id', '(' . implode(',', $fields_id) . ')', 'IN');
                $criteria->setSort('field_weight');
                $fields = $fieldHandler->getObjects($criteria, true);
                foreach ($fields as $id => $field) {
                    if (in_array($this->getVar('cid'), $field->getVar('cids'))) {
                        $ret['fields'][$id]['title'] = $field->getVar('field_title');
                        if (is_object($GLOBALS['xoopsUser'])) {
                            $ret['fields'][$id]['value'] = htmlspecialchars_decode($field->getOutputValue($GLOBALS['xoopsUser'], $extras));
                        } elseif (!is_object($GLOBALS['xoopsUser'])) {
                            $ret['fields'][$id]['value'] = htmlspecialchars_decode($extras->getVar($field->getVar('field_name')));
                        }
                    }
                }
            }
        }

        if (false === $extra) {
            return $ret;
        }

        if (0 != $this->getVar('cid')) {
            $categoryHandler = xoops_getModuleHandler('category', 'songlist');
            $category        = $categoryHandler->get($this->getVar('cid'));
            $ret['category'] = $category->toArray(false);
        }

        if (0 != count($this->getVar('gids'))) {
            $i            = 0;
            $genreHandler = xoops_getModuleHandler('genre', 'songlist');
            $ret['genre'] = '';
            $genres       = $genreHandler->getObjects(new \Criteria('gid', '(' . implode(',', $this->getVar('gids')) . ')', 'IN'), true);
            foreach ($genres as $gid => $genre) {
                $ret['genre_array'][$gid] = $genre->toArray(false);
                ++$i;
                $ret['genre'] .= $genre->getVar('name') . ($i < count($genres) ? ', ' : '');
            }
        }
        if (0 != $this->getVar('vcid')) {
            $voiceHandler = xoops_getModuleHandler('voice', 'songlist');
            $voice        = $voiceHandler->get($this->getVar('vcid'));
            $ret['voice'] = $voice->toArray(false);
        }

        if (0 != count($this->getVar('aids'))) {
            $artistsHandler = xoops_getModuleHandler('artists', 'songlist');
            foreach ($this->getVar('aids') as $aid) {
                $artist                     = $artistsHandler->get($aid);
                $ret['artists_array'][$aid] = $artist->toArray(false);
            }
        }

        if (0 != $this->getVar('abid')) {
            $albumsHandler = xoops_getModuleHandler('albums', 'songlist');
            $albums        = $albumsHandler->get($this->getVar('abid'));
            $ret['album']  = $albums->toArray(false);
        }

        return $ret;
    }

    public function getURL()
    {
        global $file, $op, $fct, $id, $value, $vcid, $gid, $cid, $start, $limit;
        if ($GLOBALS['songlistModuleConfig']['htaccess']) {
            return XOOPS_URL . '/' . $GLOBALS['songlistModuleConfig']['baseofurl'] . '/index/' . urlencode(str_replace([' ', chr(9)], '-', $this->getVar('title'))) . '/item-item-' . $this->getVar('sid') . $GLOBALS['songlistModuleConfig']['endofurl'];
        } else {
            return XOOPS_URL . '/modules/songlist/index.php?op=item&fct=item&id=' . $this->getVar('sid') . '&value=' . urlencode($value) . '&vcid=' . $vcid . '&gid=' . $gid . '&cid=' . $cid;
        }
    }
}

class SonglistSongsHandler extends XoopsPersistableObjectHandler
{
    public function __construct($db)
    {
        $moduleHandler                   = xoops_getHandler('module');
        $configHandler                   = xoops_getHandler('config');
        $GLOBALS['songlistModule']       = $moduleHandler->getByDirname('songlist');
        $GLOBALS['songlistModuleConfig'] = $configHandler->getConfigList($GLOBALS['songlistModule']->getVar('mid'));

        parent::__construct($db, 'songlist_songs', 'SonglistSongs', 'sid', 'title');
    }

    public function filterFields()
    {
        return ['sid', 'cid', 'mp3', 'gid', 'vcid', 'aids', 'abid', 'songid', 'title', 'lyrics', 'hits', 'rank', 'votes', 'tags', 'created', 'updated'];
    }

    public function getFilterCriteria($filter)
    {
        $parts    = explode('|', $filter);
        $criteria = new \CriteriaCompo();
        foreach ($parts as $part) {
            $var = explode(',', $part);
            if (!empty($var[1]) && !is_numeric($var[0])) {
                $object = $this->create();
                if (XOBJ_DTYPE_TXTBOX == $object->vars[$var[0]]['data_type']
                    || XOBJ_DTYPE_TXTAREA == $object->vars[$var[0]]['data_type']) {
                    $criteria->add(new \Criteria('`' . $var[0] . '`', '%' . $var[1] . '%', (isset($var[2]) ? $var[2] : 'LIKE')));
                } elseif (XOBJ_DTYPE_INT == $object->vars[$var[0]]['data_type']
                          || XOBJ_DTYPE_DECIMAL == $object->vars[$var[0]]['data_type']
                          || XOBJ_DTYPE_FLOAT == $object->vars[$var[0]]['data_type']) {
                    $criteria->add(new \Criteria('`' . $var[0] . '`', $var[1], (isset($var[2]) ? $var[2] : '=')));
                } elseif (XOBJ_DTYPE_ENUM == $object->vars[$var[0]]['data_type']) {
                    $criteria->add(new \Criteria('`' . $var[0] . '`', $var[1], (isset($var[2]) ? $var[2] : '=')));
                } elseif (XOBJ_DTYPE_ARRAY == $object->vars[$var[0]]['data_type']) {
                    $criteria->add(new \Criteria('`' . $var[0] . '`', '%"' . $var[1] . '";%', (isset($var[2]) ? $var[2] : 'LIKE')));
                }
            } elseif (!empty($var[1]) && is_numeric($var[0])) {
                $criteria->add(new \Criteria($var[0], $var[1]));
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

    public function insert(\XoopsObject $obj, $force = true, $object = null)
    {
        if ($obj->isNew()) {
            $new = true;
            $old = $this->create();
            $obj->setVar('created', time());
        } else {
            $new = false;
            $old = $this->get($obj->getVar('sid'));
            $obj->setVar('updated', time());
        }

        $albumsHandler   = xoops_getModuleHandler('albums', 'songlist');
        $artistsHandler  = xoops_getModuleHandler('artists', 'songlist');
        $genreHandler    = xoops_getModuleHandler('genre', 'songlist');
        $voiceHandler    = xoops_getModuleHandler('voice', 'songlist');
        $categoryHandler = xoops_getModuleHandler('category', 'songlist');

        if (true === $obj->vars['gid']['changed']) {
            if (true === $new || (0 != $obj->vars['gid']['value'])) {
                $genre = $genreHandler->get($obj->vars['gid']['value']);
                if (is_object($genre)) {
                    $genre->setVar('songs', $genre->getVar('songs') + 1);
                    $genreHandler->insert($genre, true, $obj);
                }
            }
            if (!$old->isNew() && $old->getVar('gid') > 0) {
                $genre = $genreHandler->get($old->vars['gid']['value']);
                if (is_object($genre)) {
                    $genre->setVar('songs', $genre->getVar('songs') - 1);
                    $genreHandler->insert($genre, true, null);
                }
            }
        }

        if (true === $obj->vars['vcid']['changed']) {
            if (true === $new || (0 != $obj->vars['vcid']['value'])) {
                $voice = $voiceHandler->get($obj->vars['vcid']['value']);
                if (is_object($voice)) {
                    $voice->setVar('songs', $voice->getVar('songs') + 1);
                    $voiceHandler->insert($voice, true, $obj);
                }
            }
            if (!$old->isNew() && $old->getVar('vcid') > 0) {
                $voice = $voiceHandler->get($old->vars['vcid']['value']);
                if (is_object($voice)) {
                    $voice->setVar('songs', $voice->getVar('songs') - 1);
                    $voiceHandler->insert($voice, true, null);
                }
            }
        }

        if (true === $obj->vars['cid']['changed']) {
            if (true === $new || (0 != $obj->vars['cid']['value'])) {
                $category = $categoryHandler->get($obj->vars['cid']['value']);
                if (is_object($category)) {
                    $category->setVar('songs', $category->getVar('songs') + 1);
                    $categoryHandler->insert($category, true, $obj);
                    foreach ($obj->getVar('aids') as $aid) {
                        $artists                   = $artistsHandler->get($aid);
                        $cids                      = $artists->getVar('cids');
                        $cids[$obj->getVar('cid')] = $obj->getVar('cid');
                        if (is_object($artists)) {
                            $artists->setVar('cids', $cids);
                            $artistsHandler->insert($artists, true, null);
                        }
                    }
                }
            }
            if (!$old->isNew() && $old->getVar('cid') > 0) {
                $category = $categoryHandler->get($old->vars['cid']['value']);
                if (is_object($category)) {
                    $category->setVar('songs', $category->getVar('songs') - 1);
                    $categoryHandler->insert($category, true, null);
                    foreach ($obj->getVar('aids') as $aid) {
                        $artists = $artistsHandler->get($aid);
                        $cids    = [];
                        foreach ($artists->getVar('cids') as $cid) {
                            if ($cid != $old->getVar('cid') || $cid == $obj->getVar('cid')) {
                                $cids[$cid] = $cid;
                            }
                        }
                        if (is_object($artists)) {
                            $artists->setVar('cids', $cids);
                            $artistsHandler->insert($artists, true, null);
                        }
                    }
                }
            }
        }

        if (true === $obj->vars['aids']['changed'] && 0 != count($obj->vars['aids']['value'])) {
            if (true === $new || 0 != count($obj->vars['aids']['value'])) {
                foreach ($obj->vars['aids']['value'] as $aid) {
                    if (!in_array($aid, $old->vars['aids']['value'])) {
                        $artists = $artistsHandler->get($aid);
                        if (is_object($artists)) {
                            $artists->setVar('songs', $artists->getVar('songs') + 1);
                            $artistsHandler->insert($artists, true, $obj);
                            if (true === $new || (0 != $obj->vars['vcid']['value'])) {
                                $voice = $voiceHandler->get($obj->vars['vcid']['value']);
                                if (is_object($voice)) {
                                    $voice->setVar('artists', $voice->getVar('artists') + 1);
                                    $voiceHandler->insert($voice, true, $obj);
                                }
                            }
                        }
                    }
                }
            }
            if (!$old->isNew() && 0 == count($old->getVar('aids'))) {
                foreach ($old->getVar('aids') as $aid) {
                    if (!in_array($aid, $obj->vars['aids']['value'])) {
                        $artists = $artistsHandler->get($aid);
                        if (is_object($artists)) {
                            $artists->setVar('songs', $artists->getVar('songs') - 1);
                            $artistsHandler->insert($artists, true, null);
                            if (!$old->isNew() && $old->getVar('vcid') > 0) {
                                $voice = $voiceHandler->get($old->vars['vcid']['value']);
                                if (is_object($voice)) {
                                    $voice->setVar('artists', $voice->getVar('artists') - 1);
                                    $voiceHandler->insert($voice, true, null);
                                }
                            }
                        }
                    }
                }
            }
        }

        if (true === $obj->vars['abid']['changed']) {
            if (true === $new || (0 != $obj->vars['abid']['value'])) {
                $album = $albumsHandler->get($obj->vars['abid']['value']);
                if (is_object($album)) {
                    $album->setVar('songs', $album->getVar('songs') + 1);
                    $albumsHandler->insert($album, true, $obj);
                    if (true === $new || (0 != $obj->vars['vcid']['value'])) {
                        $voice = $voiceHandler->get($obj->vars['vcid']['value']);
                        if (is_object($voice)) {
                            $voice->setVar('albums', $voice->getVar('albums') + 1);
                            $voiceHandler->insert($voice, true, $obj);
                        }
                    }
                }
            }
            if (!$old->isNew() && $old->getVar('abid') > 0) {
                $album = $albumsHandler->get($obj->vars['abid']['value']);
                if (is_object($album)) {
                    $album->setVar('songs', $album->getVar('songs') - 1);
                    $albumsHandler->insert($album, true, null);
                    if (!$old->isNew() && $old->getVar('vcid') > 0) {
                        $voice = $voiceHandler->get($old->vars['vcid']['value']);
                        if (is_object($voice)) {
                            $voice->setVar('albums', $voice->getVar('albums') - 1);
                            $voiceHandler->insert($voice, true, null);
                        }
                    }
                }
            }
        }

        if (0 == strlen($obj->getVar('title'))) {
            return false;
        }

        $sid = parent::insert($obj, $force);
        if ($obj->vars['abid']['value'] > 0) {
            $album      = $albumsHandler->get($obj->vars['abid']['value']);
            $arry       = $album->getVar('sids');
            $arry[$sid] = $sid;
            if (is_object($album)) {
                $album->setVar('sids', $arry);
                $albumsHandler->insert($album);
            }
        }
        if (count($obj->getVar('aids')) > 0) {
            foreach ($obj->getVar('aids') as $aid) {
                $artist     = $artistsHandler->get($aid);
                $arry       = $artist->getVar('sids');
                $arry[$sid] = $sid;
                if (is_object($artists)) {
                    $artist->setVar('sids', $arry);
                    $artistsHandler->insert($artist);
                }
            }
        }

        return $sid;
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

    public function getURL()
    {
        global $file, $op, $fct, $id, $value, $gid, $vid, $vcid, $cid, $start, $limit;
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

    public function getSearchURL()
    {
        global $file, $op, $fct, $id, $value, $gid, $vcid, $cid, $start, $limit;
        if ($GLOBALS['songlistModuleConfig']['htaccess']) {
            return XOOPS_URL . '/' . $GLOBALS['songlistModuleConfig']['baseofurl'] . '/' . $file . '/' . $start . '-' . $op . '-' . $fct . '-' . $gid . '-' . (isset($_GET['cid']) ? ($_GET['cid']) : $cid) . '-' . $vcid . '/' . urlencode($value) . $GLOBALS['songlistModuleConfig']['endofurl'];
        } else {
            return XOOPS_URL . '/modules/songlist/' . $file . '.php?op=' . $op . '&fct=' . $fct . '&value=' . urlencode($value) . '&cid=' . $cid . '&gid=' . $gid . '&vcid=' . $vcid . '&start=' . $start;
        }
    }

    public function getTop($limit = 1)
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
