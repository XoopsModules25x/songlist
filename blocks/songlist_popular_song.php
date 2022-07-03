<?php declare(strict_types=1);

use XoopsModules\Songlist\Helper;

/**
 * @param $options
 * @return array|null
 */
function b_songlist_popular_song_show($options): ?array
{
    xoops_loadLanguage('blocks', 'songlist');
    $handler = Helper::getInstance()->getHandler('Songs');
    $objects = $handler->getTop(1);
    if (is_object($objects[0])) {
        return $objects[0]->toArray(true);
    }

    return null;
}

/**
 * @param $options
 */
function b_songlist_popular_song_edit($options): void
{
}
