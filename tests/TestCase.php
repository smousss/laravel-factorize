<?php

namespace Smousss\Laravel\Factorize\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Smousss\Laravel\Factorize\FactorizeServiceProvider;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [FactorizeServiceProvider::class];
    }
}
