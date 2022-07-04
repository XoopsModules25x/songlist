<?php declare(strict_types=1);

namespace XoopsModules\Songlist;

require_once \dirname(__DIR__) . '/include/songlist.object.php';

// require_once \dirname(__DIR__) . '/include/songlist.form.php';
use  XoopsModules\Songlist\Form\FormController;

/**
 * Class Genre
 */
class Genre extends \XoopsObject
{
    public $gid;
    public $name;
    public $artists;
    public $albums;
    public $songs;
    public $hits;
    public $rank;
    public $votes;
    public $created;
    public $updated;

    /**
     * Genre constructor.
     * @param null $fid
     */
    public function __construct($fid = null)
    {
        $this->initVar('gid', \XOBJ_DTYPE_INT, 0, false);
        $this->initVar('name', \XOBJ_DTYPE_TXTBOX, null, false, 128);
        $this->initVar('artists', \XOBJ_DTYPE_INT, 0, false);
        $this->initVar('albums', \XOBJ_DTYPE_INT, 0, false);
        $this->initVar('songs', \XOBJ_DTYPE_INT, 0, false);
        $this->initVar('hits', \XOBJ_DTYPE_INT, 0, false);
        $this->initVar('rank', \XOBJ_DTYPE_DECIMAL, 0, false);
        $this->initVar('votes', \XOBJ_DTYPE_INT, 0, false);
        $this->initVar('created', \XOBJ_DTYPE_INT, 0, false);
        $this->initVar('updated', \XOBJ_DTYPE_INT, 0, false);
    }

    /**
     * @param bool $as_array
     * @return array|string
     */
    public function getForm($as_array = false)
    {
        return FormController::getFormGenre($this, $as_array);
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $ret  = parent::toArray();
        $form = $this->getForm(true);
        foreach ($form as $key => $element) {
            $ret['form'][$key] = $element->render();
        }
        foreach (['created', 'updated'] as $key) {
            if ($this->getVar($key) > 0) {
                $ret['form'][$key] = \date(_DATESTRING, $this->getVar($key));
                $ret[$key]         = \date(_DATESTRING, $this->getVar($key));
            }
        }
        $ret['rank'] = \number_format(($this->getVar('rank') > 0 && $this->getVar('votes') > 0 ? $this->getVar('rank') / $this->getVar('votes') : 0), 2) . \_MI_SONGLIST_OFTEN;

        return $ret;
    }

    /**
     * @return string
     */
    public function getURL(): string
    {
        global $file, $op, $fct, $id, $value, $gid, $cid, $start, $limit;
        if ($GLOBALS['songlistModuleConfig']['htaccess']) {
            return XOOPS_URL
                   . '/'
                   . $GLOBALS['songlistModuleConfig']['baseurl']
                   . '/'
                   . $file
                   . '/'
                   . \urlencode(\str_replace([' ', \chr(9)], '-', $this->getVar('name')))
                   . '/'
                   . $op
                   . '-'
                   . $fct
                   . '-'
                   . $this->getVar('gid')
                   . '-'
                   . \urlencode($value)
                   . '-'
                   . $gid
                   . '-'
                   . $cid
                   . $GLOBALS['songlistModuleConfig']['endofurl'];
        }

        return XOOPS_URL . '/modules/songlist/' . $file . '.php?op=' . $op . '&fct=' . $fct . '&id=' . $this->getVar('gid') . '&value=' . \urlencode($value ?? '') . '&gid=' . $gid . '&cid=' . $cid;
    }
}
