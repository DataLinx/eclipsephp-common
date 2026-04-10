<?php

use Eclipse\Common\Admin\Filament\Clusters\Settings;
use Eclipse\Common\CommonPlugin;

it('can get and set settings cluster', function () {
    $plugin = new CommonPlugin;

    expect($plugin->getSettingsCluster())->toBe(Settings::class);

    $plugin->settingsCluster('NewCluster');
    expect($plugin->getSettingsCluster())->toBe('NewCluster');

    $plugin->settingsCluster(null);
    expect($plugin->getSettingsCluster())->toBeNull();
});
