<?php

namespace Workbench\App\Filament\Pages;

use BackedEnum;
use Eclipse\Common\Filament\Clusters\Settings;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Workbench\App\Settings\ExampleSettings;

class ManageExampleSettings extends SettingsPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string $settings = ExampleSettings::class;

    protected static ?string $cluster = Settings::class;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('number_setting')
                    ->numeric()
                    ->integer(),
                TextInput::make('string_setting'),
            ]);
    }
}
