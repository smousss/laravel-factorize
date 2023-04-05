<?php

namespace Smousss\Laravel\Factorize;

use Spatie\LaravelPackageTools\Package;
use Smousss\Laravel\Factorize\Commands\FactorizeCommand;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FactorizeServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package) : void
    {
        $package
            ->name('factorize')
            ->hasConfigFile()
            ->hasCommand(FactorizeCommand::class);
    }
}
