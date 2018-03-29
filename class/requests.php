<?php

defined('XOOPS_ROOT_PATH') || die('Restricted access');

include_once(dirname(__DIR__) . '/include/songlist.object.php');
include_once(dirname(__DIR__) . '/include/songlist.form.php');

class SonglistRequests extends XoopsObject
{
    public function __construct($fid = null)
    {
        $this->initVar('rid', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('aid', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('artist', XOBJ_DTYPE_TXTBOX, null, false, 128);
        $this->initVar('album', XOBJ_DTYPE_TXTBOX, null, false, 128);
        $this->initVar('title', XOBJ_DTYPE_TXTBOX, null, false, 128);
        $this->initVar('lyrics', XOBJ_DTYPE_OTHER, null, false);
        $this->initVar('uid', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('name', XOBJ_DTYPE_TXTBOX, null, false, 128);
        $this->initVar('email', XOBJ_DTYPE_TXTBOX, null, false, 255);
        $this->initVar('songid', XOBJ_DTYPE_TXTBOX, null, false, 32);
        $this->initVar('sid', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('created', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('updated', XOBJ_DTYPE_INT, 0, false);
    }

    public function getForm($as_array = false)
    {
        return songlist_requests_get_form($this, $as_array);
    }

    public function toArray()
    {
        $ret            = parent::toArray();
        $form           = $this->getForm(true);
        $form['songid'] = new \XoopsFormText('', $this->getVar('rid') . '[songid]', 11, 32);
        foreach ($form as $key => $element) {
            $ret['form'][$key] = $form[$key]->render();
        }
        foreach (['created', 'updated'] as $key) {
            if ($this->getVar($key) > 0) {
                $ret['form'][$key] = date(_DATESTRING, $this->getVar($key));
                $ret[$key]         = date(_DATESTRING, $this->getVar($key));
            }
        }

        return $ret;
    }
}

class SonglistRequestsHandler extends XoopsPersistableObjectHandler
{
    public function __construct($db)
    {
        parent::__construct($db, 'songlist_requests', 'SonglistRequests', 'rid', 'name');
    }

    public function filterFields()
    {
        return ['rid', 'artist', 'album', 'title', 'lyrics', 'uid', 'name', 'email', 'songid', 'sid', 'created', 'updated'];
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
            $new      = true;
            $sendmail = true;
        } else {
            $obj->setVar('updated', time());
            $new = false;
            if (true === $obj->vars['songid']['changed']) {
                $songsHandler = xoops_getModuleHandler('songs', 'songlist');
                $criteria     = new \Criteria('songid', $obj->getVar('songid'));
                $songs        = $songsHandler->getObjects($criteria, false);
                if (is_object($songs[0])) {
                    foreach ($songs[0]->getVar('aids') as $aid) {
                        $ad[] = $aid;
                    }
                    $obj->setVar('sid', $songs[0]->getVar('sid'));
                    $obj->setVar('aid', $ad[0]);
                    $sendmail = true;
                }
            }
        }
        if ($rid = parent::insert($obj, $force)) {
            if (true === $sendmail) {
                if (true === $new) {
                    xoops_loadLanguage('email', 'songlist');
                    $xoopsMailer =& getMailer();
                    $xoopsMailer->setHTML(true);
                    $xoopsMailer->setTemplateDir($GLOBALS['xoops']->path('/modules/songlist/language/' . $GLOBALS['xoopsConfig']['language'] . '/mail_templates/'));
                    $xoopsMailer->setTemplate('songlist_request_created.html');
                    $xoopsMailer->setSubject(sprintf(_MN_SONGLIST_SUBJECT_REQUESTMADE, $rid));

                    foreach (explode('|', $GLOBALS['songlistModuleConfig']['email']) as $email) {
                        $xoopsMailer->setToEmails($email);
                    }

                    $xoopsMailer->setToEmails($obj->getVar('email'));

                    $xoopsMailer->assign('SITEURL', XOOPS_URL);
                    $xoopsMailer->assign('SITENAME', $GLOBALS['xoopsConfig']['sitename']);
                    $xoopsMailer->assign('RID', $rid);
                    $xoopsMailer->assign('TITLE', $obj->getVar('title'));
                    $xoopsMailer->assign('ALBUM', $obj->getVar('album'));
                    $xoopsMailer->assign('ARTIST', $obj->getVar('artist'));
                    $xoopsMailer->assign('EMAIL', $obj->getVar('email'));
                    $xoopsMailer->assign('NAME', $obj->getVar('name'));

                    if (!$xoopsMailer->send()) {
                        xoops_error($xoopsMailer->getErrors(true), 'Email Send Error');
                    }
                } else {
                    xoops_loadLanguage('email', 'songlist');
                    $songsHandler   = xoops_getModuleHandler('songs', 'songlist');
                    $artistsHandler = xoops_getModuleHandler('artists', 'songlist');
                    $albumsHandler  = xoops_getModuleHandler('albums', 'songlist');
                    $genreHandler   = xoops_getModuleHandler('genre', 'songlist');

                    $song = $songsHandler->get($obj->getVar('sid'));
                    if (is_object($song)) {
                        $sng = $genre->getVar('title');
                    }
                    $album = $albumHandler->get($song->getVar('abid'));
                    if (is_object($album)) {
                        $alb     = $genre->getVar('title');
                        $alb_img = $genre->getImage();
                    }
                    $genre = $genreHandler->get($song->getVar('abid'));
                    if (is_object($genre)) {
                        $gen = $genre->getVar('name');
                    }
                    $artists = $artistsHandler->getObjects(new \Criteria('aid', '(' . implode(',', $song->getVar('aid')) . ')', 'IN'), false);
                    $art     = '';
                    foreach ($artists as $id => $artist) {
                        $art .= $artist->getVar('name') . ($id < count($artists) - 1 ? ', ' : '');
                    }
                    $xoopsMailer =& getMailer();
                    $xoopsMailer->setHTML(true);
                    $xoopsMailer->setTemplateDir($GLOBALS['xoops']->path('/modules/songlist/language/' . $GLOBALS['xoopsConfig']['language'] . '/mail_templates/'));
                    $xoopsMailer->setTemplate('songlist_request_updated.html');
                    $xoopsMailer->setSubject(sprintf(_MN_SONGLIST_SUBJECT_REQUESTFOUND, $rid));

                    $xoopsMailer->setToEmails($obj->getVar('email'));

                    $xoopsMailer->assign('SITEURL', XOOPS_URL);
                    $xoopsMailer->assign('SITENAME', $GLOBALS['xoopsConfig']['sitename']);
                    $xoopsMailer->assign('RID', $rid);
                    $xoopsMailer->assign('TITLE', $obj->getVar('title'));
                    $xoopsMailer->assign('ALBUM', $obj->getVar('album'));
                    $xoopsMailer->assign('ARTIST', $obj->getVar('artist'));
                    $xoopsMailer->assign('EMAIL', $obj->getVar('email'));
                    $xoopsMailer->assign('NAME', $obj->getVar('name'));
                    $xoopsMailer->assign('SONGID', $song->getVar('songid'));
                    $xoopsMailer->assign('SONGURL', $song->getURL());
                    $xoopsMailer->assign('FOUNDTITLE', $sng);
                    $xoopsMailer->assign('FOUNDALBUM', $alb);
                    $xoopsMailer->assign('FOUNDARTIST', $art);
                    $xoopsMailer->assign('FOUNDGENRE', $gen);
                    $xoopsMailer->assign('EMAIL', $obj->getVar('email'));
                    $xoopsMailer->assign('NAME', $obj->getVar('name'));

                    if (!$xoopsMailer->send()) {
                        xoops_error($xoopsMailer->getErrors(true), 'Email Send Error');
                    }
                }
            }
        }

        return $rid;
    }

    public function getURL()
    {
        global $file, $op, $fct, $id, $value, $gid, $cid, $start, $limit;
        if ($GLOBALS['songlistModuleConfig']['htaccess']) {
            return XOOPS_URL . '/' . $GLOBALS['songlistModuleConfig']['baseofurl'] . '/' . $file . '/' . $op . '-' . $fct . $GLOBALS['songlistModuleConfig']['endofurl'];
        } else {
            return XOOPS_URL . '/modules/songlist/' . $file . '.php?op=' . $op . '&fct=' . $fct;
        }
    }
}
