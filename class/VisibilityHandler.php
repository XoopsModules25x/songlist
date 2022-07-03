<?php declare(strict_types=1);

namespace XoopsModules\Songlist;

/*
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */

use CriteriaElement;
use XoopsDatabase;
use XoopsPersistableObjectHandler;

/**
 * @copyright      {@link https://xoops.org/ XOOPS Project}
 * @license        {@link https://www.gnu.org/licenses/gpl-2.0.html GNU GPL 2 or later}
 * @author         XOOPS Development Team, Wishcraft
 */

/**
 * Class VisibilityHandler
 */
class VisibilityHandler extends XoopsPersistableObjectHandler
{
    /**
     * VisibilityHandler constructor.
     * @param \XoopsDatabase $db
     */
    public function __construct(XoopsDatabase $db)
    {
        parent::__construct($db, 'songlist_visibility', Visibility::class, 'field_id');
    }

    /**
     * get all objects matching a condition
     *
     * @param \CriteriaElement|null $criteria {@link \CriteriaElement} to match
     * @param null                  $fields
     * @param bool                  $asObject
     * @param bool                  $id_as_key
     * @return array of objects/array <a href='psi_element://XoopsObject'>XoopsObject</a>
     * @internal param array $fields variables to fetch
     * @internal param bool $asObject flag indicating as object, otherwise as array
     * @internal param bool $id_as_key use the ID as key for the array
     */
    public function &getAll(CriteriaElement $criteria = null, $fields = null, $asObject = true, $id_as_key = true): array //getAll($criteria = null)
    {
        $limit            = null;
        $GLOBALS['start'] = null;
        $sql              = "SELECT * FROM `{$this->table}`";
        if (($criteria instanceof \CriteriaCompo) || ($criteria instanceof \Criteria)) {
            $sql     .= ' ' . $criteria->renderWhere();
            $groupby = $criteria->getGroupby();
            if ($groupby) {
                $sql .= ' ' . $groupby;
            }
            $sort = $criteria->getSort();
            if ($sort) {
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
    public function getVisibleFields($profile_groups, $user_groups = []): array
    {
        $profile_groups[] = '0';
        $user_groups[]    = '0';
        $profile_groups[] = $user_groups[] = 0;
        $sql              = "SELECT field_id FROM {$this->table} WHERE profile_group IN (" . \implode(',', $profile_groups) . ')';
        $sql              .= ' AND user_group IN (' . \implode(',', $user_groups) . ')';
        $field_ids        = [];
        $result           = $this->db->query($sql);
        if ($result) {
            while ([$field_id] = $this->db->fetchRow($result)) {
                $field_ids[] = $field_id;
            }
        }

        return $field_ids;
    }
}
