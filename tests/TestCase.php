<?php

declare(strict_types=1);

namespace Mchekhashvili\Countries\Tests;

use Mchekhashvili\Countries\CountriesServiceProvider;
use Orchestra\Testbench\TestCase as Testbench;

abstract class TestCase extends Testbench
{
    protected function getPackageProviders($app): array
    {
        return [CountriesServiceProvider::class];
    }
}
