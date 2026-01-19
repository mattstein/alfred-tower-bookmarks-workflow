<?php

/**
 * Parse a Tower bookmark group (folder), recursively if necessary,
 * adding individual bookmarks to $this->parsedBookmarks.
 *
 * @param array $arr group
 * @param       $parsedBookmarks
 * @return void
 */
function parseGroup(array $arr, &$parsedBookmarks): void
{
    foreach ($arr as $item) {
        if (isGroup($item)) {
            parseGroup($item['children'], $parsedBookmarks);
        } else {
            $parsedBookmarks[] = $item;
        }
    }
}

/**
 * Parse nested bookmarks into flat array.
 *
 * @param array $arr multidimensional bookmark array read from Tower plist and converted by CFPropertyList
 * @return array      flat array of bookmark items
 */
function parseItems(array $arr, $parsedBookmarks): array
{
    parseGroup($arr, $parsedBookmarks);

    return $parsedBookmarks;
}

/**
 * Determine whether the current item (array) is a Tower bookmark group.
 *
 * @param array $item element from multi-dimensional array of bookmarks
 * @return boolean      true if item is a folder (has `children` property)
 */
function isGroup(array $item): bool
{
    return isset($item['children']);
}
