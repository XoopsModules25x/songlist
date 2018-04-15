<?php

/*
Module: Songlist

Version: 3.23

Description: Object manager for WHMCS Billing

Author: Written by Simon Roberts aka. Wishcraft (simon@chronolabs.coop)

Owner: Frilogg

License: See docs - End User Licence.pdf
*/

defined('XOOPS_ROOT_PATH') || die('XOOPS root path not defined');

class SonglistVisibility extends \XoopsObject
{
    public function __construct()
    {
        $this->initVar('field_id', XOBJ_DTYPE_INT);
        $this->initVar('user_group', XOBJ_DTYPE_INT);
        $this->initVar('profile_group', XOBJ_DTYPE_INT);
    }

    public function SonglistVisibility()
    {
        $this->__construct();
    }
}

class SonglistVisibilityHandler extends \XoopsPersistableObjectHandler
{
    public function __construct($db)
    {
        parent::__construct($db, 'songlist_visibility', 'SonglistVisibility', 'field_id');
    }

    /**
     * get all objects matching a condition
     *
     * @param   object $criteria {@link CriteriaElement} to match
     * @return array of objects/array <a href='psi_element://XoopsObject'>XoopsObject</a>
     * @internal param array $fields variables to fetch
     * @internal param bool $asObject flag indicating as object, otherwise as array
     * @internal param bool $id_as_key use the ID as key for the array
     */
    public function getAll($criteria = null)
    {
        $limit            = null;
        $GLOBALS['start'] = null;
        $sql              = "SELECT * FROM `{$this->table}`";
        if (isset($criteria) && is_subclass_of($criteria, 'criteriaelement')) {
            $sql .= ' ' . $criteria->renderWhere();
            if ($groupby = $criteria->getGroupby()) {
                $sql .= ' ' . $groupby;
            }
            if ($sort = $criteria->getSort()) {
                $sql      .= " ORDER BY {$sort} " . $criteria->getOrder();
                $orderSet = true;
            }
            $limit            = $criteria->getLimit();
            $GLOBALS['start'] = $criteria->getStart();
        }
        if (empty($orderSet)) {
            $sql .= " ORDER BY `{$this->keyName}` DESC";
        }
        $result = $this->db->query($sql, $limit, $GLOBALS['start']);
        $ret    = [];
        while (false !== ($row = $this->db->fetchArray($result))) {
            $ret[$row['field_id']][] = $row;
        }

        return $ret;
    }

    /**
     * Get fields visible to the $user_groups on a $profile_groups profile
     *
     * @param array $profile_groups groups of the user to be accessed
     * @param array $user_groups    groups of the visitor, default as $GLOBALS['xoopsUser']
     *
     * @return array
     */
    public function getVisibleFields($profile_groups, $user_groups = [])
    {
        $profile_groups   = array_merge($profile_groups, ['0']);
        $user_groups      = array_merge($user_groups, ['0']);
        $profile_groups[] = $user_groups[] = 0;
        $sql              = "SELECT field_id FROM {$this->table} WHERE profile_group IN (" . implode(',', $profile_groups) . ')';
        $sql              .= ' AND user_group IN (' . implode(',', $user_groups) . ')';
        $field_ids        = [];
        if ($result = $this->db->query($sql)) {
            while (false !== (list($field_id) = $this->db->fetchRow($result))) {
                $field_ids[] = $field_id;
            }
        }

        return $field_ids;
    }
}
