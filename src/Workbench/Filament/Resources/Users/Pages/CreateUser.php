<?php

namespace Eclipse\Common\Workbench\Filament\Resources\Users\Pages;

use Eclipse\Common\Workbench\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
}
