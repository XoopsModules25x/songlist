<?php declare(strict_types=1);

use XoopsModules\Songlist\Helper;

/**
 * @param $options
 * @return array|null
 */
function b_songlist_popular_artists_show($options): ?array
{
    xoops_loadLanguage('blocks', 'songlist');
    $handler = Helper::getInstance()->getHandler('Artists');
    $objects = $handler->getTop($options[0]);
    if (count($objects) > 0) {
        $ret = [];
        foreach ($objects as $id => $object) {
            $ret[$id] = $object->toArray(true); //TODO mb why do we need toArray()?
        }

        return $ret;
    }

    return null;
}

/**
 * @param $options
 * @return string
 */
function b_songlist_popular_artists_edit($options): string
{
    xoops_load('XoopsFormLoader');
    xoops_loadLanguage('blocks', 'songlist');
    $num = new \XoopsformText('', 'options[0]', 10, 10, $options[0]);

    return _BL_SONGLIST_NUMBEROFITEMS . $num->render();
}
