<?php

namespace Eclipse\Common\Foundation\Providers;

use Eclipse\Common\Package;
use Spatie\LaravelPackageTools\Package as SpatiePackage;

abstract class PackageServiceProvider extends \Spatie\LaravelPackageTools\PackageServiceProvider
{
    abstract public function configurePackage(Package|SpatiePackage $package): void;

    public function newPackage(): Package
    {
        return new Package;
    }
}
