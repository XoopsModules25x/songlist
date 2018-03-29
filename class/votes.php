<?php

defined('XOOPS_ROOT_PATH') || die('Restricted access');

include_once(dirname(__DIR__) . '/include/songlist.object.php');
include_once(dirname(__DIR__) . '/include/songlist.form.php');

class SonglistVotes extends XoopsObject
{
    public function __construct($fid = null)
    {
        $this->initVar('vid', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('sid', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('uid', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('ip', XOBJ_DTYPE_TXTBOX, null, false, 64);
        $this->initVar('netaddy', XOBJ_DTYPE_TXTBOX, null, false, 255);
        $this->initVar('rank', XOBJ_DTYPE_DECIMAL, 0, false);
    }

    public function getForm($as_array = false)
    {
        return songlist_votes_get_form($this, $as_array);
    }

    public function toArray()
    {
        $ret  = parent::toArray();
        $form = $this->getForm(true);
        foreach ($form as $key => $element) {
            $ret['form'][$key] = $form[$key]->render();
        }

        $ret['rank'] = number_format($this->getVar('rank'), 2) . _MI_SONGLIST_OFTEN;

        return $ret;
    }
}

class SonglistVotesHandler extends XoopsPersistableObjectHandler
{
    public function __construct($db)
    {
        parent::__construct($db, 'songlist_votes', 'SonglistVotes', 'vid', 'ip');
    }

    public function filterFields()
    {
        return ['vid', 'sid', 'uid', 'ip', 'netaddy', 'rank'];
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

    public function insert(\XoopsObject $obj, $force = true)
    {
        if ($obj->isNew()) {
            $obj->setVar('created', time());
        } else {
            $obj->setVar('updated', time());
        }

        return parent::insert($obj, $force);
    }

    public function addVote($sid, $value)
    {
        $criteria = new \CriteriaCompo(new \Criteria('sid', $sid));

        $ip = songlist_getIPData(false);
        if ($ip['uid'] > 0) {
            $criteria->add(new \Criteria('uid', $ip['uid']));
        } else {
            $criteria->add(new \Criteria('ip', $ip['ip']));
            $criteria->add(new \Criteria('netaddy', $ip['network-addy']));
        }

        if (0 == $this->getCount($criteria) && $sid > 0 && $value > 0) {
            $vote = $this->create();
            $vote->setVar('sid', $sid);
            $vote->setVar('uid', $ip['uid']);
            $vote->setVar('ip', $ip['ip']);
            $vote->setVar('netaddy', $ip['network-addy']);
            $vote->setVar('rank', $value);
            if ($this->insert($vote)) {
                $songsHandler    = xoops_getModuleHandler('songs', 'songlist');
                $albumsHandler   = xoops_getModuleHandler('albums', 'songlist');
                $artistsHandler  = xoops_getModuleHandler('artists', 'songlist');
                $categoryHandler = xoops_getModuleHandler('category', 'songlist');
                $genreHandler    = xoops_getModuleHandler('genre', 'songlist');
                $voiceHandler    = xoops_getModuleHandler('voice', 'songlist');

                $song  = $songsHandler->get($sid);
                $sql   = [];
                $sql[] = 'UPDATE `' . $songsHandler->table . '` SET `rank` = `rank` + ' . $value . ', `votes` = `votes` + 1 WHERE `' . $songsHandler->keyName . '` = ' . $sid;
                $sql[] = 'UPDATE `' . $categoryHandler->table . '` SET `rank` = `rank` + ' . $value . ', `votes` = `votes` + 1 WHERE `' . $categoryHandler->keyName . '` = ' . $song->getVar($categoryHandler->keyName);
                $sql[] = 'UPDATE `' . $genreHandler->table . '` SET `rank` = `rank` + ' . $value . ', `votes` = `votes` + 1 WHERE `' . $genreHandler->keyName . '` = ' . $song->getVar($genreHandler->keyName);
                $sql[] = 'UPDATE `' . $voiceHandler->table . '` SET `rank` = `rank` + ' . $value . ', `votes` = `votes` + 1 WHERE `' . $voiceHandler->keyName . '` = ' . $song->getVar($voiceHandler->keyName);
                $sql[] = 'UPDATE `' . $albumsHandler->table . '` SET `rank` = `rank` + ' . $value . ', `votes` = `votes` + 1 WHERE `' . $albumsHandler->keyName . '` = ' . $song->getVar($albumsHandler->keyName);
                foreach ($song->getVar('aids') as $aid) {
                    $sql[] = 'UPDATE `' . $artistsHandler->table . '` SET `rank` = `rank` + ' . $value . ', `votes` = `votes` + 1 WHERE `' . $artistsHandler->keyName . '` = ' . $aid;
                }
                foreach ($sql as $question) {
                    $GLOBALS['xoopsDB']->queryF($question);
                }
                redirect_header($_POST['uri'], 10, _MN_SONGLIST_MSG_VOTED_FINISHED);
                exit(0);
            } else {
                redirect_header($_POST['uri'], 10, _MN_SONGLIST_MSG_VOTED_ALREADY);
                exit(0);
            }
        } else {
            redirect_header($_POST['uri'], 10, _MN_SONGLIST_MSG_VOTED_SOMETHINGWRONG);
            exit(0);
        }

        return false;
    }
}
