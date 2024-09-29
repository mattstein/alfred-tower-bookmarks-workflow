<?php

require_once('vendor/autoload.php');

use Alfred\Workflows\Workflow;

$parsedBookmarks = [];
$workflow = new Workflow();
$bookmarkFile = $workflow->env('HOME') . "/Library/Application Support/com.fournova.Tower3/bookmarks-v2.plist";

if (! file_exists($bookmarkFile)) {
    $workflow->logger()->log('Bookmark file is missing.');
    return;
}

$xml = simplexml_load_string(file_get_contents($file));

$collectedItems = [];
$bookmarkNodes = $xml->dict[0]->array[0] ?? null;

if ( ! $bookmarkNodes) {
    $workflow->logger()->log('No bookmarks found.');
    return;
}

$query = trim($workflow->argument() ?? '');
parseGroup($bookmarkNodes, $collectedItems);

$results = [];

foreach ($collectedItems as $item) {
    if (str_contains($item['name'], $query)) {
        $results[] = $item;
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

function parseGroup($nodes, &$collectedItems): void
{
    foreach ($nodes as $node) {
        if (isBookmark($node)) {
            $collectedItems[] = [
                'fileURL' => (string)$node->string[0],
                'name' => (string)$node->string[1],
            ];
        } else if (isFolder($node)) {
            parseGroup($node->array->dict, $collectedItems);
        }
    }
}

function isBookmark($node): bool
{
    return (int)$node->integer === 2;
}
function isFolder($node): bool
{
    return (int)$node->integer === 1;
}