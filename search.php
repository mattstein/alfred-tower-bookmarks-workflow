<?php

require_once('vendor/autoload.php');
require_once('functions.php');

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
