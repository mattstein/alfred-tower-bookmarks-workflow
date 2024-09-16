<?php

require_once('vendor/autoload.php');

use Alfred\Workflows\Workflow;
use CFPropertyList\CFPropertyList;

$parsedBookmarks = [];
$workflow = new Workflow();
$bookmarkFile = $workflow->env('HOME') . "/Library/Application Support/com.fournova.Tower3/bookmarks-v2.plist";

try {
    $bookmarkData = new CFPropertyList($bookmarkFile, CFPropertyList::FORMAT_XML);
} catch (Exception $e) {
    $workflow->logger()->log('Couldn’t parse bookmark file: ' . $e->getMessage());
    return;
}

$results = [];

$query = trim($workflow->argument() ?? '');
$bookmarks = parseItems($bookmarkData->toArray()['children'], $parsedBookmarks);

foreach ($bookmarks as $bookmark) {
    if (str_contains($bookmark['name'], $query)) {
        $results[] = $bookmark;
    }
}

foreach ($results as $hit) {
    $folderPath = str_replace('file://', '', $hit['fileURL']);

    $workflow->item()
        ->title($hit['name'])
        ->subtitle($folderPath)
        ->iconForFilePath('/Applications/Tower.app')
        ->arg($folderPath);
}

$workflow->output();

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
