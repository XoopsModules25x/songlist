<?php declare(strict_types=1);

namespace XoopsModules\Songlist;

require_once \dirname(__DIR__) . '/include/songlist.object.php';

// require_once \dirname(__DIR__) . '/include/songlist.form.php';
use  XoopsModules\Songlist\Form\FormController;

/**
 * Class Votes
 */
class Votes extends \XoopsObject
{
    public $vid;
    public $sid;
    public $uid;
    public $ip;
    public $netaddy;
    public $rank;

    /**
     * Votes constructor.
     * @param null $fid
     */
    public function __construct($fid = null)
    {
        $this->initVar('vid', \XOBJ_DTYPE_INT, 0, false);
        $this->initVar('sid', \XOBJ_DTYPE_INT, 0, false);
        $this->initVar('uid', \XOBJ_DTYPE_INT, 0, false);
        $this->initVar('ip', \XOBJ_DTYPE_TXTBOX, null, false, 64);
        $this->initVar('netaddy', \XOBJ_DTYPE_TXTBOX, null, false, 255);
        $this->initVar('rank', \XOBJ_DTYPE_DECIMAL, 0, false);
    }

    /**
     * @param bool $as_array
     * @return array|string
     */
    public function getForm($as_array = false)
    {
        return FormController::votes_get_form($this, $as_array);
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

        $ret['rank'] = \number_format($this->getVar('rank'), 2) . \_MI_SONGLIST_OFTEN;

        return $ret;
    }
}
