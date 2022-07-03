<?php declare(strict_types=1);

use XoopsModules\Songlist\Helper;

/**
 * @param $options
 * @return array|null
 */
function b_songlist_popular_artist_show($options): ?array
{
    xoops_loadLanguage('blocks', 'songlist');
    $handler = Helper::getInstance()->getHandler('Artists');
    $objects = $handler->getTop(1);
    if (is_object($objects[0])) {
        return $objects[0]->toArray(true);
    }

    return null;
}

/**
 * @param $options
 */
function b_songlist_popular_artist_edit($options): void
{
}
