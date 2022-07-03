<?php declare(strict_types=1);

namespace XoopsModules\Songlist;

/**
 * Class Extras
 */
class Extras extends \XoopsObject
{
    public $handler;

    /**
     * Extras constructor.
     * @param $fields
     */
    public function __construct($fields)
    {
        $this->initVar('sid', \XOBJ_DTYPE_INT, null, true);
        $this->init($fields);
    }

    /**
     * Initiate variables
     * @param array $fields field information array of {@link \XoopsObjectsField} objects
     */
    public function init($fields): void
    {
        if ($fields && \is_array($fields)) {
            foreach (\array_keys($fields) as $key) {
                $this->initVar($key, $fields[$key]->getVar('field_valuetype'), $fields[$key]->getVar('field_default', 'n'), $fields[$key]->getVar('field_required'), $fields[$key]->getVar('field_maxlength'));
            }
        }
    }
}
