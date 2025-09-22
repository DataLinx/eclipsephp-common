<?php

use Eclipse\Catalogue\Models\Group;
use Eclipse\Common\Foundation\Models\Scopes\ActiveScope;

test('active scope works', function () {

    // Create an inactive product group
    $group = Group::factory()->inactive()->create();

    // Test scope being applied
    expect(Group::where('id', $group->id)->count())->toBe(0);

    // Test scope not being applied
    expect(Group::withoutGlobalScope(ActiveScope::class)->where('id', $group->id)->count())->toBe(1);
});
