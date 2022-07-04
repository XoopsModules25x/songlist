<?php declare(strict_types=1);

namespace XoopsModules\Songlist;

use XoopsFormText;
use XoopsObject;

require_once \dirname(__DIR__) . '/include/songlist.object.php';

// require_once \dirname(__DIR__) . '/include/songlist.form.php';
use  XoopsModules\Songlist\Form\FormController;

/**
 * Class Requests
 */
class Requests extends XoopsObject
{
    public $rid;
    public $aid;
    public $artist;
    public $album;
    public $title;
    public $lyrics;
    public $uid;
    public $name;
    public $email;
    public $songid;
    public $sid;
    public $created;
    public $updated;

    /**
     * Requests constructor.
     * @param null $fid
     */
    public function __construct($fid = null)
    {
        $this->initVar('rid', \XOBJ_DTYPE_INT, 0, false);
        $this->initVar('aid', \XOBJ_DTYPE_INT, 0, false);
        $this->initVar('artist', \XOBJ_DTYPE_TXTBOX, null, false, 128);
        $this->initVar('album', \XOBJ_DTYPE_TXTBOX, null, false, 128);
        $this->initVar('title', \XOBJ_DTYPE_TXTBOX, null, false, 128);
        $this->initVar('lyrics', \XOBJ_DTYPE_OTHER, null, false);
        $this->initVar('uid', \XOBJ_DTYPE_INT, 0, false);
        $this->initVar('name', \XOBJ_DTYPE_TXTBOX, null, false, 128);
        $this->initVar('email', \XOBJ_DTYPE_TXTBOX, null, false, 255);
        $this->initVar('songid', \XOBJ_DTYPE_TXTBOX, null, false, 32);
        $this->initVar('sid', \XOBJ_DTYPE_INT, 0, false);
        $this->initVar('created', \XOBJ_DTYPE_INT, 0, false);
        $this->initVar('updated', \XOBJ_DTYPE_INT, 0, false);
    }

    /**
     * @param bool $as_array
     * @return array|string
     */
    public function getForm($as_array = false)
    {
        return FormController::getFormRequests($this, $as_array);
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $ret            = parent::toArray();
        $form           = $this->getForm(true);
        $form['songid'] = new XoopsFormText('', $this->getVar('rid') . '[songid]', 11, 32);
        foreach ($form as $key => $element) {
            $ret['form'][$key] = $element->render();
        }
        foreach (['created', 'updated'] as $key) {
            if ($this->getVar($key) > 0) {
                $ret['form'][$key] = \date(\_DATESTRING, $this->getVar($key));
                $ret[$key]         = \date(\_DATESTRING, $this->getVar($key));
            }
        }

        return $ret;
    }
}
