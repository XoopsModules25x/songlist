<?php declare(strict_types=1);

namespace XoopsModules\Songlist\Form;

/*
 You may not change or alter any portion of this comment or credits
 of supporting developers from this source code or any supporting source code
 which is considered copyrighted (c) material of the original comment or credit authors.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
*/

/*
Module: Xcenter
Version: 2.01
Description: Multilingual Content Module with tags and lists with search functions
Author: Written by Simon Roberts aka. Wishcraft (simon@chronolabs.coop)
Owner: Chronolabs
License: See /docs - GPL 2.0
*/

use Xmf\Request;
use XoopsFormElement;
use XoopsModules\Songlist\Helper;

/**
 *  Xoops Form Class Elements
 *
 * @copyright       XOOPS Project (https://xoops.org)
 * @license         https://www.fsf.org/copyleft/gpl.html GNU public license
 * @author          Kazumi Ono <onokazu@xoops.org>
 * @author          Taiwen Jiang <phppp@users.sourceforge.net>
 * @author          John Neill <catzwolf@xoops.org>
 * @version         $Id: formselect.php 3988 2009-12-05 15:46:47Z trabis $
 */
\xoops_load('XoopsFormElement');

/**
 * A select field
 *
 * @author      Kazumi Ono <onokazu@xoops.org>
 * @author      Taiwen Jiang <phppp@users.sourceforge.net>
 * @author      John Neill <catzwolf@xoops.org>
 * @copyright   XOOPS Project (https://xoops.org)
 */
class SelectCategoryForm extends XoopsFormElement
{
    /**
     * Options
     *
     * @var array
     */
    public $_options = [];
    /**
     * Allow multiple selections?
     *
     * @var bool
     */
    public $_multiple = false;
    /**
     * Number of rows. "1" makes a dropdown list.
     *
     * @var int
     */
    public $_size;
    /**
     * Pre-selcted values
     *
     * @var array
     */
    public $_value = [];

    /**
     * Constructor
     *
     * @param string $caption  Caption
     * @param string $name     "name" attribute
     * @param mixed  $value    Pre-selected value (or array of them).
     * @param int    $size     Number of rows. "1" makes a drop-down-list
     * @param bool   $multiple Allow multiple selections?
     * @param int    $ownid
     */
    public function __construct($caption, $name, $value = null, $size = 1, $multiple = false, $ownid = 0)
    {
        global $_form_object_options;
        \xoops_loadLanguage('modinfo', 'songlist');

        $this->setCaption($caption);
        $this->setName($name);
        $this->_multiple = $multiple;
        $this->_size     = (int)$size;
        if (isset($value)) {
            $this->setValue($value);
        }
        $this->addOption('0', \_MI_SONGLIST_ALL);
        if (!isset($_form_object_options['category'])) {
            $_form_object_options['category'] = $this->getCategory(0);
        }
        //        if (Request::hasVar('category', 'form_object_options')) {
        if (isset($_form_object_options['category'])) {
            $this->populateList($_form_object_options['category'], $ownid);
        }
    }

    /**
     * @param     $vars
     * @param int $ownid
     */
    public function populateList($vars, $ownid = 0): void
    {
        foreach ($vars as $previd => $cats) {
            if ($previd != $ownid || 0 == $ownid) {
                foreach ($cats as $catid => $title) {
                    if ($catid != $ownid || 0 == $ownid) {
                        $this->addOption($catid, $title['item']);
                        if (isset($title['sub'])) {
                            $this->populateList($title['sub'], $ownid);
                        }
                    }
                }
            }
        }
    }

    /**
     * @param $ownid
     * @return array
     */
    public function getCategory($ownid): array
    {
        $categoryHandler = Helper::getInstance()->getHandler('Category');
        $criteria        = new \Criteria('pid', '0');
        $criteria->setSort('name');
        $criteria->setOrder('ASC');
        $categories  = $categoryHandler->getObjects($criteria, true);
        $langs_array = $this->treeMenu([], $categories, -1, $ownid);

        return $langs_array;
    }

    /**
     * @param     $langs_array
     * @param     $categories
     * @param     $level
     * @param     $ownid
     * @param int $previd
     * @return array
     */
    public function treeMenu($langs_array, $categories, $level, $ownid, $previd = 0): array
    {
        ++$level;
        $categoryHandler = Helper::getInstance()->getHandler('Category');
        foreach ($categories as $catid => $category) {
            if ($catid != $ownid) {
                $langs_array[$previd][$catid]['item'] = \str_repeat('--', $level) . $category->getVar('name');
                $criteria                             = new \Criteria('pid', $catid);
                $criteria->setSort('name');
                $criteria->setOrder('ASC');
                $categoriesb = $categoryHandler->getObjects($criteria, true);
                if ($categoriesb) {
                    $langs_array[$previd][$catid]['sub'] = $this->treeMenu($langs_array, $categoriesb, $level, $ownid, $catid);
                }
            }
        }
        $level--;

        return $langs_array;
    }

    /**
     * Are multiple selections allowed?
     *
     * @return bool
     */
    public function isMultiple(): bool
    {
        return $this->_multiple;
    }

    /**
     * Get the size
     *
     * @return int
     */
    public function getSize(): int
    {
        return $this->_size;
    }

    /**
     * Get an array of pre-selected values
     *
     * @param bool $encode To sanitizer the text?
     * @return array
     */
    public function getValue($encode = false): array
    {
        if (!$encode) {
            return $this->_value;
        }
        $value = [];
        foreach ($this->_value as $val) {
            $value[] = $val ? \htmlspecialchars($val, \ENT_QUOTES) : $val;
        }

        return $value;
    }

    /**
     * Set pre-selected values
     *
     * @param mixed $value
     */
    public function setValue($value): void
    {
        if (\is_array($value)) {
            foreach ($value as $v) {
                $this->_value[] = (int)$v;
            }
//            $this->_value[] = array_values($value);
        } elseif (isset($value)) {
            $this->_value[] = $value;
        }
    }

    /**
     * Add an option
     *
     * @param string $value "value" attribute
     * @param string $name  "name" attribute
     */
    public function addOption($value, $name = ''): void
    {
        if ('' != $name) {
            $this->_options[$value] = $name;
        } else {
            $this->_options[$value] = $value;
        }
    }

    /**
     * Add multiple options
     *
     * @param array $options Associative array of value->name pairs
     */
    public function addOptionArray($options): void
    {
        if (\is_array($options)) {
            foreach ($options as $k => $v) {
                $this->addOption($k, $v);
            }
        }
    }

    /**
     * Get an array with all the options
     *
     * Note: both name and value should be sanitized. However for backward compatibility, only value is sanitized for now.
     *
     * @param bool|int $encode To sanitizer the text? potential values: 0 - skip; 1 - only for value; 2 - for both value and name
     * @return array Associative array of value->name pairs
     */
    public function getOptions($encode = false): array
    {
        if (!$encode) {
            return $this->_options;
        }
        $value = [];
        foreach ($this->_options as $val => $name) {
            $value[$encode ? \htmlspecialchars($val, \ENT_QUOTES) : $val] = ($encode > 1) ? \htmlspecialchars($name, \ENT_QUOTES) : $name;
        }

        return $value;
    }

    /**
     * Prepare HTML for output
     *
     * @return string HTML
     */
    public function render(): string
    {
        $ele_name    = $this->getName();
        $ele_title   = $this->getTitle();
        $ele_value   = $this->getValue();
        $ele_options = $this->getOptions();
        $ret         = '<select size="' . $this->getSize() . '"' . $this->getExtra();
        if ($this->isMultiple()) {
            $ret .= ' name="' . $ele_name . '[]" id="' . $ele_name . '" title="' . $ele_title . '" multiple="multiple">';
        } else {
            $ret .= ' name="' . $ele_name . '" id="' . $ele_name . '" title="' . $ele_title . '">';
        }
        foreach ($ele_options as $value => $name) {
            $ret .= '<option value="' . \htmlspecialchars((string)$value, \ENT_QUOTES) . '"';
            if (\count($ele_value) > 0 && \in_array($value, $ele_value, true)) {
                $ret .= ' selected';
            }
            $ret .= '>' . $name . '</option>';
        }
        $ret .= '</select>';

        return $ret;
    }

    /**
     * Render custom javascript validation code
     *
     * @seealso XoopsForm::renderValidationJS
     */
    public function renderValidationJS()
    {
        // render custom validation code if any
        if (!empty($this->customValidationCode)) {
            return \implode("\n", $this->customValidationCode);
            // generate validation code if required
        }

        if ($this->isRequired()) {
            $eltname    = $this->getName();
            $eltcaption = $this->getCaption();
            $eltmsg     = empty($eltcaption) ? \sprintf(_FORM_ENTER, $eltname) : \sprintf(_FORM_ENTER, $eltcaption);
            $eltmsg     = \str_replace('"', '\"', \stripslashes($eltmsg));

            return "\nvar hasSelected = false; var selectBox = myform.{$eltname};"
                   . 'for (i = 0; i < selectBox.options.length; i++ ) { if (selectBox.options[i].selected === true) { hasSelected = true; break; } }'
                   . "if (!hasSelected) { window.alert(\"{$eltmsg}\"); selectBox.focus(); return false; }";
        }

        return '';
    }
}
