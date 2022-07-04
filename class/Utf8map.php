<?php declare(strict_types=1);

namespace XoopsModules\Songlist;

use XoopsObject;

\defined('XOOPS_ROOT_PATH') || exit('Restricted access');

require_once \dirname(__DIR__) . '/include/songlist.object.php';

// require_once \dirname(__DIR__) . '/include/songlist.form.php';
use  XoopsModules\Songlist\Form\FormController;

/**
 * Class Utf8map
 */
class Utf8map extends XoopsObject
{
    public $utfid;
    public $from;
    public $to;
    public $created;
    public $updated;

    /**
     * Utf8map constructor.
     * @param null $fid
     */
    public function __construct($fid = null)
    {
        $this->initVar('utfid', \XOBJ_DTYPE_INT, 0, false);
        $this->initVar('from', \XOBJ_DTYPE_TXTBOX, null, false, 2);
        $this->initVar('to', \XOBJ_DTYPE_TXTBOX, null, false, 2);
        $this->initVar('created', \XOBJ_DTYPE_INT, 0, false);
        $this->initVar('updated', \XOBJ_DTYPE_INT, 0, false);
    }

    /**
     * @param bool $as_array
     * @return array|string
     */
    public function getForm($as_array = false)
    {
        return FormController::getFormUtf8map($this, $as_array);
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

        return $ret;
    }
}
