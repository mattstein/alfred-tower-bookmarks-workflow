<?php

require_once __DIR__ . '/../../functions.php';

test('isGroup returns true for items with children', function () {
    $group = [
        'name' => 'Work Projects',
        'children' => [],
    ];

    expect(isGroup($group))->toBeTrue();
});

test('isGroup returns false for bookmark items', function () {
    $bookmark = [
        'name' => 'My Repository',
        'fileURL' => 'file:///path/to/repo',
    ];

    expect(isGroup($bookmark))->toBeFalse();
});

test('parseItems flattens nested bookmarks', function () {
    $nested = [
        [
            'name' => 'Work',
            'children' => [
                ['name' => 'Project A', 'fileURL' => 'file:///work/a'],
                ['name' => 'Project B', 'fileURL' => 'file:///work/b'],
            ],
        ],
        ['name' => 'Personal', 'fileURL' => 'file:///personal'],
    ];

    $result = parseItems($nested, []);

    expect($result)->toHaveCount(3)
        ->and($result[0]['name'])->toBe('Project A')
        ->and($result[1]['name'])->toBe('Project B')
        ->and($result[2]['name'])->toBe('Personal');
});

test('parseItems handles deeply nested groups', function () {
    $deep = [
        [
            'name' => 'Level 1',
            'children' => [
                [
                    'name' => 'Level 2',
                    'children' => [
                        ['name' => 'Deep', 'fileURL' => 'file:///deep'],
                    ],
                ],
            ],
        ],
    ];

    $result = parseItems($deep, []);

    expect($result)->toHaveCount(1)
        ->and($result[0]['name'])->toBe('Deep');
});

test('parseItems returns empty array for empty input', function () {
    expect(parseItems([], []))->toBeEmpty();
});
